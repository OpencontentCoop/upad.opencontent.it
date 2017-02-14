<?php

class OCCrossSearch
{
    const SERVER_BASE_PATH = '/repository/server/';

    const CLIENT_BASE_PATH = '/repository/client/';

    /**
     * @param int $repositoryID
     *
     * @return OCRepositoryServerInterface
     * @throws Exception
     */
    public static function serverHandler( $repositoryID )
    {
        $ini = eZINI::instance( 'ocrepository.ini' );
        if ( $ini->hasGroup( 'Server_' . $repositoryID ) )
        {
            $handlerName = $ini->variable( 'Server_' . $repositoryID, 'Handler' );            
            if ( class_exists( $handlerName ) )
            {
                $parameters = $ini->hasVariable( 'Server_' . $repositoryID, 'Parameters' ) ? $ini->variable( 'Server_' . $repositoryID, 'Parameters' ) : array();     
                $handler = new $handlerName( $parameters );
                if ( $handler instanceof OCRepositoryServerInterface )
                {                    
                    return $handler;
                }
            }            
        }
        throw new Exception( "Server $repositoryID non trovato" );
    }

    /**
     * @return array
     */
    public static function listAvailableRepositories()
    {
        $ini = eZINI::instance( 'ocrepository.ini' );
        $repositories = array();
        $availableRepositories = $ini->hasVariable( 'Client', 'AvailableRepositories' ) ? $ini->variable( 'Client', 'AvailableRepositories' ) : array();
        foreach ( $availableRepositories as $repositoryID )
        {
            $definition = self::isAvailableRepository( $repositoryID );
            if ( $definition )
            {                
                $repositories[] = $definition;
            }
        }
        return $repositories;
    }

    /**
     * @param int $repositoryID
     *
     * @return array|bool
     */
    public static function isAvailableRepository( $repositoryID )
    {
        $ini = eZINI::instance( 'ocrepository.ini' );
        if ( $ini->hasGroup( 'Client_' . $repositoryID ) )
        {
            $definition = $ini->group( 'Client_' . $repositoryID );
            $definition['Identifier'] = $repositoryID;
            return $definition;
        }
        return false;
    }

    /**
     * @param int $repositoryID
     *
     * @return OCRepositoryClientInterface|OCClassSearchTemplate
     * @throws Exception
     */
    public static function instanceRepository( $repositoryID )
    {
        $definition = self::isAvailableRepository( $repositoryID );        
        if ( !$definition )
        {
            throw new Exception( "Non trovo il repository $repositoryID" );
        }
        
        $serverInfoUrl = rtrim( $definition['Url'], '/' ) .  self::SERVER_BASE_PATH . $repositoryID;        
        $definition['ServerBaseUrl'] = $serverInfoUrl;
        $definition['ClientBasePath'] = self::CLIENT_BASE_PATH . $repositoryID;
        if ( !eZHTTPTool::getDataByURL( $serverInfoUrl, true ) )
        {            
            throw new Exception( "Repository $repositoryID ($serverInfoUrl) non raggiungibile" );
        }
        
        $serverInfo = json_decode( eZHTTPTool::getDataByURL( $serverInfoUrl ), true );        
        if ( !$serverInfo )
        {
            throw new Exception( "Il repository $repositoryID non ha risposto correttamente alla richiesta di informazioni" );
        }        
        if ( isset( $serverInfo['error'] ) )
        {
            throw new Exception( "Errore del server remoto: \" {$serverInfo['error']} \" " );
        }
        if ( $serverInfo['type']  )
        {
            if ( isset( $definition['Handler'] ) )
            {
                $clientHandlerName = $definition['Handler'];
            }
            else
            {
                $clientHandlerName = 'OCRepository' . $serverInfo['type'] . 'Client';
            }
            if ( class_exists( $clientHandlerName ) )
            {
                $clientHandler = new $clientHandlerName();
                if ( !$clientHandler instanceof OCRepositoryClientInterface )
                {
                    throw new Exception( "La libreria $clientHandlerName non estende l'interfaccia corretta" );
                }
                $parameters = array();
                if ( $serverInfo['parameters'] )
                {
                    $parameters = $serverInfo['parameters'];
                }
                $parameters['definition'] = $definition;
                $clientHandler->init( $parameters );
                return $clientHandler;
            }
        }
        
        throw new Exception( "Errore" );
    }
}
