<?php

/**
 * Classe LdapAuth
 * 
 * Autenticação via Active Directory usando extensão LDAP nativa do PHP.
 * Versão standalone sem dependências do Laravel.
 * 
 * Baseada em AUTENTICAÇÃO/app/Services/AuthService.php
 */
class LdapAuth
{
    private $ldapConfig;
    
    /**
     * Construtor - Carrega configurações LDAP
     */
    public function __construct()
    {
        $this->loadConfig();
    }
    
    /**
     * Carrega configurações do arquivo ldap.php ou usa valores padrão
     */
    private function loadConfig()
    {
        $configPath = __DIR__ . '/../AUTENTICAÇÃO/config/ldap.php';
        
        if (file_exists($configPath)) {
            $this->ldapConfig = require $configPath;
        } else {
            // Valores padrão se o arquivo não existir
            $this->ldapConfig = [
                'host' => '10.68.56.2',
                'port' => 389,
                'timeout' => 5,
                'base_dn' => 'DC=BANT,DC=INTRAER',
                'account_suffix' => '@BANT.INTRAER',
                'admin_username' => 'admin.bancada@BANT.INTRAER',
                'admin_password' => '#123456789#',
                'use_ssl' => false,
                'use_tls' => false,
                'username_attribute' => 'sAMAccountName',
            ];
        }
        
        // Processar valores de env() se necessário (simulação simples)
        $this->ldapConfig['host'] = $this->getEnvValue('LDAP_HOSTS', $this->ldapConfig['host'] ?? '10.68.56.2');
        $this->ldapConfig['port'] = (int)($this->getEnvValue('LDAP_PORT', $this->ldapConfig['port'] ?? 389));
        $this->ldapConfig['base_dn'] = $this->getEnvValue('LDAP_BASE_DN', $this->ldapConfig['base_dn'] ?? 'DC=BANT,DC=INTRAER');
        $this->ldapConfig['account_suffix'] = $this->getEnvValue('LDAP_ACCOUNT_SUFFIX', $this->ldapConfig['account_suffix'] ?? '@BANT.INTRAER');
    }
    
    /**
     * Obtém valor de variável de ambiente ou retorna padrão
     */
    private function getEnvValue($key, $default)
    {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
    
    /**
     * Autentica um usuário via Active Directory
     * 
     * @param string $login Login do AD
     * @param string $password Senha do AD
     * @return bool Retorna true se autenticação bem-sucedida, false caso contrário
     */
    public function authenticate($login, $password)
    {
        $ldapConn = null;
        
        try {
            // Verifica se a extensão LDAP está disponível
            if (!function_exists('ldap_connect')) {
                error_log('[LdapAuth] Extensão LDAP não está disponível no PHP');
                return false;
            }
            
            // Configurações do AD
            $ldapHost = $this->ldapConfig['host'];
            $ldapPort = $this->ldapConfig['port'];
            $baseDn = $this->ldapConfig['base_dn'];
            $accountSuffix = $this->ldapConfig['account_suffix'];
            
            // Conecta ao servidor LDAP
            $ldapConn = @ldap_connect($ldapHost, $ldapPort);
            
            if (!$ldapConn) {
                error_log('[LdapAuth] Não foi possível conectar ao servidor LDAP: ' . $ldapHost);
                return false;
            }
            
            // Configura opções LDAP
            ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);
            
            // Busca informações do usuário no AD antes de autenticar
            $userDn = null;
            $userUpn = null;
            
            $adminUsername = $this->ldapConfig['admin_username'] ?? null;
            $adminPassword = $this->ldapConfig['admin_password'] ?? null;
            
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
            $usernameAttribute = $this->ldapConfig['username_attribute'] ?? 'sAMAccountName';
            $searchFilter = "({$usernameAttribute}=" . $this->ldapEscape($login) . ")";
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
                    error_log('[LdapAuth] Autenticação LDAP bem-sucedida: ' . $login . ' (formato: ' . $usernameFormat . ')');
                    break;
                }
            }
            
            if (!$bindSucesso) {
                error_log('[LdapAuth] Tentativa de login AD falhou: ' . $login . ' (erro: ' . ldap_error($ldapConn) . ')');
                ldap_close($ldapConn);
                return false;
            }
            
            ldap_close($ldapConn);
            return true;
            
        } catch (Exception $e) {
            error_log('[LdapAuth] Erro ao autenticar no AD: ' . $e->getMessage());
            
            if ($ldapConn) {
                @ldap_close($ldapConn);
            }
            
            return false;
        }
    }
    
    /**
     * Formata o username para autenticação no AD
     * 
     * @param string $login Login fornecido
     * @return string Login formatado
     */
    private function formatUsername($login)
    {
        // Se já contém @, retorna como está
        if (strpos($login, '@') !== false) {
            return $login;
        }
        
        // Caso contrário, adiciona o sufixo do domínio da configuração
        $accountSuffix = $this->ldapConfig['account_suffix'] ?? '@BANT.INTRAER';
        return $login . $accountSuffix;
    }
    
    /**
     * Escapa caracteres especiais para filtros LDAP
     * 
     * @param string $value Valor a ser escapado
     * @return string Valor escapado
     */
    private function ldapEscape($value)
    {
        // Escapa caracteres especiais do LDAP
        $chars = ['\\', '*', '(', ')', '/', "\x00"];
        $replace = ['\\5c', '\\2a', '\\28', '\\29', '\\2f', '\\00'];
        return str_replace($chars, $replace, $value);
    }
}

