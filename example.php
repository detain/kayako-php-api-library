<?php
require_once("kyIncludes.php");

/**
 * Initialization.
 */
//client initialization
kyBase::init("http://mykayako.example.com/api/index.php", "<API key>", "<Secret key>");

/**
 * Optional. Setting defaults for new tickets.
 * WARNING:
 * Object identifiers may be different in your installation.
 * To find out proper identifier fetch all objects and examine them with:
 * print kyTicketStatus::getAll();
 * print kyTicketPriority::getAll();
 * print kyTicketType::getAll();
 */

$default_status_id = 1;
$default_priority_id = 1;
$default_type_id = 1;
kyTicket::setDefaults($default_status_id, $default_priority_id, $default_type_id);

/**
 * Creating ticket.
 */

/**
 * Load the department, user and staff.
 * WARNING:
 * Object identifiers may be different in your installation.
 * To find out proper identifier fetch all objects and examine them with:
 * print kyDepartment::getAll();
 * print kyUser::getAll();
 * print kyStaff::getAll();
 * Also make sure that:
 * - the user has the right to create ticket in the department,
 * - the staff user has right to be assigned to tickets in the department,
 * - department type is Public and it's module is set to "Tickets".
 */
$department_id = 1;
$user_id = 1;
$staff_id = 1;
$department = kyDepartment::get($department_id);
$user = kyUser::get($user_id);
$staff = kyStaff::get($staff_id);

//create ticket
$new_ticket = $user->newTicket($department,
"Lorem ipsum dolor sit amet, consectetur adipiscing elit.
Aliquam placerat cursus augue sed adipiscing. Proin viverra egestas nulla et sollicitudin.",
	"Lorem ipsum 1")->create();

//print the ticket Display Identifier
print "The ticket was created and its ID is: ".$new_ticket->getDisplayId();

/**
 * Assign staff user.
 */

//assign staff user
$new_ticket->setOwnerStaff($staff)->update();

/**
 * Adding ticket post (as assigned staff user).
 */

//add new post
$new_ticket_post = $new_ticket->newPost($new_ticket->getOwnerStaff(), "What??")->create();

/**
 * Adding ticket post with attachment (as user).
 */

//add new post
$new_ticket_post = $new_ticket->newPost($new_ticket->getUser(), "Sorry, I forgot the attachment...")->create();

//add attachment to the post - change the path to the proper file
$new_ticket_attachment = $new_ticket_post->newAttachmentFromFile("/path/to/file.pdf")->create();

/**
 * Other changes.
 */

//record time spent on processing the ticket
$new_ticket->newTimeTrack("Worked hard on this", $staff, "1:30", "00:45")->create();

//change status
$new_ticket->setStatusId(2)->update();

/**
 * Searching for tickets (using getAll).
 */
$tickets = kyTicket::getAll(array(1), array(1, 2), array(1), array(1));


/**
 * Searching for tickets (using query).
 */
$tickets = kyTicket::search("Lorem ipsum", array(kyTicket::SEARCH_CONTENTS, kyTicket::SEARCH_NOTES));

/**
 * Filtering, sorting and paging results.
 */

//print available filter methods for User objects
print_r(kyUser::getAvailableFilterMethods());

//print available order methods for Staff objects
print_r(kyStaff::getAvailableOrderMethods());

//find the user with email someuser@example.com
$user = kyUser::getAll()->filterByEmail("someuser@example.com")->first();

//find ticket time tracks with billable time greater than 10 minutes and sort them ascending using time worked
$time_tracks = $new_ticket->getTimeTracks()->filterByTimeBillable(array(">", 10 * 60))->orderByTimeWorked();

//find department with title "General"
$general_department = kyDepartment::getAll()->filterByTitle("General")->first();

//find and print list of tickets in "General" department with word "help" in subject
print kyTicket::getAll($general_department->getId())->filterBySubject(array("~", "help"));

//assuming 10 items per page, get second page from list of staff users ordered by fullname
$staff_page_2 = kyStaff::getAll()->orderByFullName()->getPage(2, 10);
