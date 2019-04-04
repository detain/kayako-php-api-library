<?php
/**
 * Includes all Library files.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @package Common
 */

//interfaces
require_once("kyRESTClientInterface.php");

//default REST client
require_once("kyRESTClient.php");

//base classes
require_once("kyObjectBase.php");
require_once("kyObjectWithCustomFieldsBase.php");
require_once("kyCustomFieldGroupBase.php");
require_once("kyCommentBase.php");
require_once("kyCommentableBase.php");

//config
require_once("kyConfig.php");

//helpers
require_once("kyHelpers.php");

//API objects
require_once("kyCustomField.php");
require_once("kyCustomFieldDefinition.php");
require_once("kyCustomFieldOption.php");
require_once("kyDepartment.php");
require_once("kyNewsCategory.php");
require_once("kyNewsComment.php");
require_once("kyNewsItem.php");
require_once("kyNewsSubscriber.php");
require_once("kyStaff.php");
require_once("kyStaffGroup.php");
require_once("kyTicket.php");
require_once("kyTicketAttachment.php");
require_once("kyTicketCustomFieldGroup.php");
require_once("kyTicketNote.php");
require_once("kyTicketPost.php");
require_once("kyTicketPriority.php");
require_once("kyTicketStatus.php");
require_once("kyTicketTimeTrack.php");
require_once("kyTicketType.php");
require_once("kyUser.php");
require_once("kyUserGroup.php");
require_once("kyUserOrganization.php");
require_once("kyKnowledgebaseCategory.php");
require_once("kyKnowledgebaseArticle.php");
require_once("kyKnowledgebaseAttachment.php");
require_once("kyKnowledgebaseComment.php");
require_once("kyTroubleshooterCategory.php");
require_once("kyTroubleshooterStep.php");
require_once("kyTroubleshooterComment.php");
require_once("kyTroubleshooterAttachment.php");

//client-side implementations
require_once("kyCustomFieldDate.php");
require_once("kyCustomFieldFile.php");
require_once("kyCustomFieldSelect.php");
require_once("kyCustomFieldLinkedSelect.php");
require_once("kyCustomFieldMultiSelect.php");

//other
require_once("kyResultSet.php");
