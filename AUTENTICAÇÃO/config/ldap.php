<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Active Directory Configuration
    |--------------------------------------------------------------------------
    | Configuração para autenticação via Active Directory usando extensão LDAP nativa do PHP.
    | 
    | Domínio: BANT.INTRAER
    | Controlador de Domínio: 10.68.56.2
    | 
    | Esta configuração é utilizada pelo AuthService para autenticação LDAP.
    */

    /*
    |--------------------------------------------------------------------------
    | Servidor LDAP
    |--------------------------------------------------------------------------
    */

    'host' => '10.68.56.2',

    'port' => '389',

    'timeout' => '5',

    /*
    |--------------------------------------------------------------------------
    | Base DN (Distinguished Name)
    |--------------------------------------------------------------------------
    | Base DN do Active Directory onde os usuários estão localizados.
    */

    'base_dn' => 'DC=BANT,DC=INTRAER',

    /*
    |--------------------------------------------------------------------------
    | Configurações de Autenticação
    |--------------------------------------------------------------------------
    */

    'account_suffix' => '@BANT.INTRAER',

    /*
    |--------------------------------------------------------------------------
    | Credenciais de Administrador (Opcional)
    |--------------------------------------------------------------------------
    | Usado apenas se necessário para buscas no AD antes da autenticação.
    | Normalmente não é necessário para autenticação simples via bind.
    */

    'admin_username' => 'admin.bancada@BANT.INTRAER',

    'admin_password' => '#123456789#',

    /*
    |--------------------------------------------------------------------------
    | Opções de Conexão
    |--------------------------------------------------------------------------
    */

    'use_ssl' => false,

    'use_tls' => false,

    /*
    |--------------------------------------------------------------------------
    | Atributos LDAP
    |--------------------------------------------------------------------------
    | Atributos utilizados para busca e identificação de usuários.
    */

    'username_attribute' => 'sAMAccountName',

    'display_name_attribute' => 'displayName',

    'email_attribute' => 'mail',

    /*
    |--------------------------------------------------------------------------
    | Model de Usuário
    |--------------------------------------------------------------------------
    | Model que representa o usuário autenticado no sistema.
    */

    'user_model' => App\Models\Militar::class,

    /*
    |--------------------------------------------------------------------------
    | Configurações de Log
    |--------------------------------------------------------------------------
    */

    'log_enabled' => true,

    'log_channel' => 'stack',

];


