<?php

namespace App\Services;

use App\Models\Militar;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Service: AuthService
 * 
 * Gerencia a autenticação do sistema com dois modos:
 * 
 * - DESENVOLVIMENTO (APP_ENV=local): Autenticação interna usando senhas do banco de dados
 * - PRODUÇÃO (APP_ENV=production): Autenticação via Active Directory usando extensão LDAP nativa
 * 
 * O modo é selecionado automaticamente baseado no ambiente da aplicação.
 */
class AuthService
{
    /**
     * Autentica um usuário via Active Directory (produção) ou internamente (desenvolvimento)
     * 
     * @param string $login Login do AD ou interno
     * @param string $password Senha do AD ou interna
     * @return Militar|null Retorna o militar autenticado ou null
     */
    public function authenticate(string $login, string $password): ?Militar
    {
        // Verifica o ambiente: em produção usa LDAP, em desenvolvimento usa autenticação interna
        if (app()->environment('production')) {
            return $this->authenticateLDAP($login, $password);
        } else {
            return $this->authenticateInternal($login, $password);
        }
    }

    /**
     * Autenticação via Active Directory (Produção)
     * 
     * @param string $login Login do AD
     * @param string $password Senha do AD
     * @return Militar|null
     */
    protected function authenticateLDAP(string $login, string $password): ?Militar
    {
        $ldapConn = null;
        
        try {
            // Verifica se a extensão LDAP está disponível
            if (!function_exists('ldap_connect')) {
                // Tenta registrar o erro, mas não falha se o log não estiver disponível
                try {
                    Log::error('Extensão LDAP não está disponível no PHP');
                } catch (\Exception $logException) {
                    // Ignora erros de log para não quebrar a aplicação
                }
                return null;
            }

            // Configurações do AD (lê do arquivo de configuração)
            $ldapHost = config('ldap.host', '10.68.56.2');
            $ldapPort = config('ldap.port', 389);
            $baseDn = config('ldap.base_dn', 'DC=BANT,DC=INTRAER');
            $accountSuffix = config('ldap.account_suffix', '@BANT.INTRAER');
            
            // Conecta ao servidor LDAP
            $ldapConn = @ldap_connect($ldapHost, $ldapPort);
            
            if (!$ldapConn) {
                try {
                    Log::error('Não foi possível conectar ao servidor LDAP', ['host' => $ldapHost]);
                } catch (\Exception $logException) {
                    // Ignora erros de log
                }
                return null;
            }

            // Configura opções LDAP
            ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

            // Busca informações do usuário no AD antes de autenticar
            // Tenta usar credenciais de admin se disponíveis, senão tenta bind anônimo
            $userDn = null;
            $userUpn = null;
            
            $adminUsername = config('ldap.admin_username');
            $adminPassword = config('ldap.admin_password');
            
            if ($adminUsername && $adminPassword) {
                // Tenta bind com admin para buscar o usuário
                $adminBind = @ldap_bind($ldapConn, $adminUsername, $adminPassword);
                if (!$adminBind) {
                    // Se falhar, tenta bind anônimo
                    @ldap_bind($ldapConn);
                }
            } else {
                // Tenta bind anônimo
                @ldap_bind($ldapConn);
            }
            
            // Busca o usuário no AD para obter UPN e DN reais
            $usernameAttribute = config('ldap.username_attribute', 'sAMAccountName');
            $searchFilter = "({$usernameAttribute}=" . ldap_escape($login, '', LDAP_ESCAPE_FILTER) . ")";
            $searchResult = @ldap_search($ldapConn, $baseDn, $searchFilter, ['dn', 'userPrincipalName', 'sAMAccountName']);
            
            if ($searchResult) {
                $entries = ldap_get_entries($ldapConn, $searchResult);
                if ($entries['count'] > 0) {
                    $userDn = $entries[0]['dn'];
                    if (isset($entries[0]['userprincipalname'][0])) {
                        $userUpn = $entries[0]['userprincipalname'][0];
                    }
                }
            }

            // Tenta autenticação com diferentes formatos
            $bindFormats = [];
            
            // Formato 1: UPN real do AD (se encontrado) - mais confiável
            if ($userUpn) {
                $bindFormats[] = $userUpn;
            }
            
            // Formato 2: UPN formatado (user@domain.com)
            $formattedUpn = $this->formatUsername($login);
            if (!in_array($formattedUpn, $bindFormats)) {
                $bindFormats[] = $formattedUpn;
            }
            
            // Formato 3: DN completo (se encontrado)
            if ($userDn) {
                $bindFormats[] = $userDn;
            }
            
            // Formato 4: DOMAIN\username
            $domain = str_replace('@', '', $accountSuffix);
            $domainUsername = $domain . '\\' . $login;
            if (!in_array($domainUsername, $bindFormats)) {
                $bindFormats[] = $domainUsername;
            }
            
            // Formato 5: Apenas o login (alguns ADs aceitam)
            if (!in_array($login, $bindFormats)) {
                $bindFormats[] = $login;
            }
            
            // Tenta autenticação com cada formato
            $bindSucesso = false;
            foreach ($bindFormats as $usernameFormat) {
                $bind = @ldap_bind($ldapConn, $usernameFormat, $password);
                
                if ($bind) {
                    $bindSucesso = true;
                    try {
                        Log::debug('Autenticação LDAP bem-sucedida', [
                            'login' => $login,
                            'formato_usado' => $usernameFormat
                        ]);
                    } catch (\Exception $logException) {
                        // Ignora erros de log
                    }
                    break;
                }
            }
            
            if (!$bindSucesso) {
                try {
                    Log::warning('Tentativa de login AD falhou', [
                        'login' => $login,
                        'ldap_error' => ldap_error($ldapConn),
                        'formatos_tentados' => count($bindFormats)
                    ]);
                } catch (\Exception $logException) {
                    // Ignora erros de log
                }
                ldap_close($ldapConn);
                return null;
            }
            
            // Busca informações completas do usuário no AD (após autenticação)
            if (!isset($entries) || $entries['count'] == 0) {
                $searchResult = @ldap_search($ldapConn, $baseDn, $searchFilter, ['dn', 'sAMAccountName', 'userPrincipalName', 'displayName', 'mail']);
                
                if ($searchResult) {
                    $entries = ldap_get_entries($ldapConn, $searchResult);
                }
            }

            if (!isset($entries) || $entries['count'] == 0) {
                try {
                    Log::warning('Usuário não encontrado no AD após autenticação', ['login' => $login]);
                } catch (\Exception $logException) {
                    // Ignora erros de log
                }
                ldap_close($ldapConn);
                return null;
            }

            ldap_close($ldapConn);

            // Busca o militar no cadastro interno
            $militar = Militar::where('login', $login)
                ->where('ativo', true)
                ->first();

            if (!$militar) {
                try {
                    Log::warning('Usuário autenticado no AD mas não cadastrado no sistema', ['login' => $login]);
                } catch (\Exception $logException) {
                    // Ignora erros de log
                }
                return null;
            }

            // Registra login bem-sucedido
            try {
                Log::info('Login LDAP realizado com sucesso', [
                    'militar_id' => $militar->id,
                    'login' => $login,
                    'perfil' => $militar->perfil
                ]);
            } catch (\Exception $logException) {
                // Ignora erros de log, mas continua com o login
            }

            return $militar;

        } catch (\Exception $e) {
            // Tenta registrar o erro, mas não falha se o log não estiver disponível
            try {
                Log::error('Erro ao autenticar no AD', [
                    'login' => $login,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            } catch (\Exception $logException) {
                // Ignora erros de log para não quebrar a aplicação
            }
            
            if ($ldapConn) {
                @ldap_close($ldapConn);
            }
            
            return null;
        }
    }

    /**
     * Autenticação interna (Desenvolvimento)
     * 
     * @param string $login Login interno
     * @param string $password Senha interna
     * @return Militar|null
     */
    protected function authenticateInternal(string $login, string $password): ?Militar
    {
        try {
            // Busca o militar no cadastro interno
            $militar = Militar::where('login', $login)
                ->where('ativo', true)
                ->first();

            if (!$militar) {
                Log::warning('Usuário não encontrado no sistema (desenvolvimento)', ['login' => $login]);
                return null;
            }

            // Verifica se o militar tem senha configurada (hash no banco)
            // Se não tiver senha, aceita qualquer senha em desenvolvimento
            if (empty($militar->password)) {
                // Em desenvolvimento, se não houver senha configurada, aceita qualquer senha
                Log::info('Login interno realizado (sem senha configurada)', [
                    'militar_id' => $militar->id,
                    'login' => $login,
                    'perfil' => $militar->perfil
                ]);
                return $militar;
            }

            // Se tiver senha configurada, verifica usando Hash
            if (!Hash::check($password, $militar->password)) {
                Log::warning('Senha incorreta (desenvolvimento)', ['login' => $login]);
                return null;
            }

            // Registra login bem-sucedido
            Log::info('Login interno realizado com sucesso', [
                'militar_id' => $militar->id,
                'login' => $login,
                'perfil' => $militar->perfil
            ]);

            return $militar;

        } catch (\Exception $e) {
            Log::error('Erro ao autenticar internamente', [
                'login' => $login,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Formata o username para autenticação no AD
     * 
     * @param string $login Login fornecido
     * @return string Login formatado
     */
    private function formatUsername(string $login): string
    {
        // Se já contém @, retorna como está
        if (strpos($login, '@') !== false) {
            return $login;
        }

        // Caso contrário, adiciona o sufixo do domínio da configuração
        $accountSuffix = config('ldap.account_suffix', '@BANT.INTRAER');
        return $login . $accountSuffix;
    }

    /**
     * Valida se um usuário pode acessar o sistema
     * 
     * @param string $login Login do AD
     * @return bool
     */
    public function canAccess(string $login): bool
    {
        return Militar::where('login', $login)
            ->where('ativo', true)
            ->exists();
    }
}

*** End Patch

