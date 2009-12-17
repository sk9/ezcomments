<?php
//
// Send comment notification to user who subscribed the content
//
// Created on: <17-Dec-2009 13:12:00 xc>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ Publish
// SOFTWARE RELEASE: 4.3.x
// COPYRIGHT NOTICE: Copyright (C) 1999-2009 eZ Systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//
/**
 * Send comment notification to user who subscribed the content
 * // 1. check ezcomment_notification table
 * // 2. fetch content from content table and build mail content
 * // 3. send mail to notified user 
 * 
 * 
 * // To be done
 * 1. warning
 * 2. subject is empty
 * 3. delete the data
 * 4. undisclosed-recipients
 */
$cli = eZCLI::instance();
$scriopt = eZScript::instance( array( 'description' => 'eZ Publish extension ezcomments sending notification script',
                                      'use-session' => false,
                                      'use-module' => false,
                                      'use-extension' => false ) );
$script->startup();
$script->initialize();
if ( !$isQuiet )
    $cli->output( "Start sending comment notification..."  );
// 1. check ezcomment_notification table
$db = eZDB::instance();

// 2. fetch content from content table and build mail content
$now = new eZDateTime();
$currentTime = $now->toTime()->timeStamp();
//to be done: setting from setting.ini
$sendingNumber = 1;
$commentLength = 5;
$mailContentType = 'text/html';
$mailFrom = 'noreply@ez.no';

$notifications = $db->arrayQuery( 'SELECT * FROM ezcomment_notification '.
                                   'WHERE status=1' .
                                   ' AND send_time < ' . $currentTime .
                                   ' ORDER BY id ASC'. 
                                   ' LIMIT 0,'. $sendingNumber );
$notificationCount = 0;
$mailCount = 0;
foreach ( $notifications as $notification )
{
    
     $contentObjectID = $notification['contentobject_id'];
     $contentLanguage = $notification['language_id'];
     $commentID = $notification['comment_id'];
     //fetch the content from content object, this can be extended to be other content
     $contentObject = eZContentObject::fetch( $contentObjectID, true );
     
     if( is_null( $contentObject ) )
     {
         $cli->output( "Content doesn't exist, delete the notification. Content ID:" . $contentObjectID );
         removeNotification( $notification['id'] );
         continue;
     }
     
     $contentName = $contentObject->name( false, $contentLanguage );
     $comment = ezcomComment::fetch( $commentID );
     if( is_null( $comment ) )
     {
         $cli->output( "Comment doesn't exist, delete the notification. Comment ID:" . $commentID );
         removeNotification( $notification['id'] );
         continue;
     }
     
     $commentAuthor = $comment->attribute( 'name' );
     $commentTitle = $comment->attribute('title');
     $commentText = $comment->attribute('text');
     if ( isset( $commentText ) && ( $commentLength > -1 ) )
     {
         if( strlen( $commentText ) > $commentLength )
         {
            $commentText = substr( $commentText, 0 ,$commentLength ).'...';
         }
         
     }
     
     //fetch the mail address list
     $subID = $contentObjectID . '_' . $contentLanguage;
     $emailList = $db->arrayQuery( 'SELECT DISTINCT email FROM ezcomment_subscriber' .
                                      ' WHERE id IN' . 
                                      ' (SELECT subscriber_id'.
                                      ' FROM ezcomment_subscription'.
                                      ' WHERE enabled = 1'.
                                      ' AND sub_id = \''. $subID .'\')' );
     if( !is_array( $emailList ) )
     {
         $cli->output( "Subscription mail doesn't exist, delete the notification. Content ID:" . $contentObjectID );
         removeNotification( $notification['id'] );
         continue;
     }
     $emailAddressList = array();
     foreach ( $emailList as $email )
     {
        $emailAddressList[] = $email['email'];
     }
     
     //fetch mail subject and template
     
     require_once( 'kernel/common/template.php' );
     $tpl = templateInit();
     $tpl->setVariable( 'contentObject', $contentObject );
     $tpl->setVariable( 'comment', $comment );
     $mailSubject = $tpl->fetch( 'design:comment/notification_subject.tpl' );
     $mailBody = $tpl->fetch( 'design:comment/notification.tpl' );
     
     //send mail
     $mailParameters = array();
     $mailParameters['from'] = $mailFrom;
     $parameters['content_type'] = $mailContentType;
     $transport = eZNotificationTransport::instance( 'ezmail' );
     $result = $transport->send( $emailAddressList, $mailSubject, $mailBody, null, $parameters );
     
     if( $result )
     {
         //removeNotification( $notification['id'] );
         $notificationCount ++;
         $mailCount += count( $emailAddressList );
     }
     else
     {
         //handle error:to do - update database
         $cli->output( 'send mail problem in notification: '.$notification['id'] );
     }
}

$cli->output( 'Notification sent.Total email:' . $mailCount );


function removeNotification( $id )
{
    $db->query( 'DELETE FROM ezcomment_notification WHERE id=' . $id );
}

?>