<?php

class OCOperatorsCollectionsTools
{
    /**
     * @param eZContentObject $object
     * @param bool $allVersions
     * @param int $newParentNodeID
     * @throws Exception
     * @return eZContentObject
     */
    public static function copyObject( eZContentObject $object, $allVersions = false, $newParentNodeID = null )
    {
        if ( !$object instanceof eZContentObject )
            throw new InvalidArgumentException( 'Object not found' );
        
        if ( !$newParentNodeID )
            $newParentNodeID = $object->attribute( 'main_parent_node_id' );
            
    
        // check if we can create node under the specified parent node
        if( ( $newParentNode = eZContentObjectTreeNode::fetch( $newParentNodeID ) ) === null )
            throw new InvalidArgumentException( 'Parent node not found' );
    
        $classID = $object->attribute('contentclass_id');
    
        if ( !$newParentNode->checkAccess( 'create', $classID ) )
        {
            $objectID = $object->attribute( 'id' );
            eZDebug::writeError( "Cannot copy object $objectID to node $newParentNodeID, " .
                                   "the current user does not have create permission for class ID $classID",
                                 'content/copy' );
            throw new Exception( 'Object not found' );
        }
    
        $db = eZDB::instance();
        $db->begin();
        $newObject = $object->copy( $allVersions );
        // We should reset section that will be updated in updateSectionID().
        // If sectionID is 0 then the object has been newly created
        $newObject->setAttribute( 'section_id', 0 );
        $newObject->store();
    
        $curVersion        = $newObject->attribute( 'current_version' );
        $curVersionObject  = $newObject->attribute( 'current' );
        $newObjAssignments = $curVersionObject->attribute( 'node_assignments' );
        unset( $curVersionObject );
    
        // remove old node assignments
        foreach( $newObjAssignments as $assignment )
        {
            /** @var eZNodeAssignment $assignment */
            $assignment->purge();
        }
    
        // and create a new one
        $nodeAssignment = eZNodeAssignment::create( array(
                                                         'contentobject_id' => $newObject->attribute( 'id' ),
                                                         'contentobject_version' => $curVersion,
                                                         'parent_node' => $newParentNodeID,
                                                         'is_main' => 1
                                                         ) );
        $nodeAssignment->store();
    
        $db->commit();
        return $newObject;
    }
}