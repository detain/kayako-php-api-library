<?php
require_once("../kyIncludes.php");

print "<pre>";
/**
 * Initialization.
 */
kyConfig::set(new kyConfig("<API URL>", "<API key>", "<Secret key>"));
kyConfig::get()->setDebugEnabled(true);

/**
 * Optional. Setting defaults for new tickets.
 * WARNING:
 * Names may be different in your instalation.
 */

$default_status_id = kyTicketStatus::getAll()->filterByTitle("Open")->first()->getId();
$default_priority_id = kyTicketPriority::getAll()->filterByTitle("Normal")->first()->getId();
$default_type_id = kyTicketType::getAll()->filterByTitle("Issue")->first()->getId();
kyTicket::setDefaults($default_status_id, $default_priority_id, $default_type_id);

$general_department = kyDepartment::getAll()
	->filterByTitle("General")
	->filterByModule(kyDepartment::MODULE_TICKETS)
	->first();

/**
 * Cleanup - delete what's left from previous run of this example.
 */

$example_department = kyDepartment::getAll()->filterByTitle("Printers (example)");
if (count($example_department) > 0) {
	$tickets_to_delete = kyTicket::getAll($example_department)
	    ->filterBySubject("Printer not working (example)");

	$tickets_to_delete->deleteAll();

	if (count($tickets_to_delete) > 0) {
		printf("Tickets DELETED:\n%s", $tickets_to_delete);
	}
}

$users_to_delete = kyUser::getAll()
    ->filterByEmail("anno.ying@example.com");

$users_to_delete->deleteAll();

if (count($users_to_delete) > 0) {
	printf("Users DELETED:\n%s", $users_to_delete);
}

$staff_to_delete = kyStaff::getAll()
    ->filterByEmail("john.doe@lazycorp.com");

$staff_to_delete->deleteAll();

if (count($staff_to_delete) > 0) {
	printf("Staff users DELETED:\n%s", $staff_to_delete);
}

$staff_groups_to_delete = kyStaffGroup::getAll()
    ->filterByTitle("Lazy guys (example)");

$staff_groups_to_delete->deleteAll();

if (count($staff_groups_to_delete) > 0) {
	printf("Staff groups DELETED:\n%s", $staff_groups_to_delete);
}

$departments_to_delete = kyDepartment::getAll()
    ->filterByTitle(array("Urgent problems (example)", "Printers (example)"));

$departments_to_delete->deleteAll();

if (count($departments_to_delete) > 0) {
	printf("Departments DELETED:\n%s", $departments_to_delete);
}

/**
 * Load the department.
 * WARNING:
 * Department title may be different in your installation.
 */
$general_department = kyDepartment::getAll()
	->filterByTitle("General")
	->filterByModule(kyDepartment::MODULE_TICKETS)
	->first();

print 'Fetched: '.$general_department;

/**
 * Create subdepartment in General department:
 * title: Printers (example)
 * type: public (default)
 * module: tickets (default)
 * WARNING
 * It's not currently possible to assign staff groups to departments via API. You must do it using Admin Control Panel.
 */
$printers_department = $general_department
    ->newSubdepartment("Printers (example)")
    ->create();

print 'Created: '.$printers_department;

/**
 * Create some livechat department:
 * title: Urgent problems (example)
 * type: public
 * module: livechat
 */
$livechat_department = kyDepartment::createNew("Urgent problems (example)", kyDepartment::TYPE_PUBLIC, kyDepartment::MODULE_LIVECHAT)
    ->create();

print 'Created: '.$livechat_department;

/**
 * Create a staff group:
 * title: Lazy guys (example)
 * isadmin: false (default)
 */
$lazy_staff_group = kyStaffGroup::createNew("Lazy guys (example)")
    ->create();

print 'Created: '.$lazy_staff_group;

/**
 * Create a staff user in just created staff group:
 * firstname: John
 * lastname: Doe
 * username: lazyguy
 * email: john.doe@lazycorp.com
 * password: veryhardpassword
 */
$staff_user = $lazy_staff_group
    ->newStaff("John", "Doe", "lazyguy", "john.doe@lazycorp.com", "veryhardpassword")
    ->setDesignation("useless specialist") //designation
    ->setSignature("Sorry I couldn't help you") //signature
    ->create();

print 'Created: '.$staff_user;

/**
 * Update staff user mobile number.
 */
$staff_user
    ->setMobileNumber("427 078 528") //mobilenumber
    ->update();

print 'Updated: '.$staff_user;

/**
 * Load Registered user group.
 */
$registered_user_group = kyUserGroup::getAll()
    ->filterByTitle("Registered")
    ->first();

print 'Fetched: '.$registered_user_group;

/**
 * Load some user organization.
 */
$user_organization = kyUserOrganization::getAll()
    ->first();

print 'Fetched: '.$user_organization;

/**
 * Create new user in Registered group:
 * fullname: Anno Ying
 * email: anno.ying@example.com
 * password: qwerty123
 */
$user = $registered_user_group
    ->newUser("Anno Ying", "anno.ying@example.com", "qwerty123")
    ->setUserOrganization($user_organization) //userorganizationid
    ->setSalutation(kyUser::SALUTATION_MR) //salutation
    ->setSendWelcomeEmail(false) //sendwelcomeemail
    ->create();

print 'Created: '.$user;

/**
 * Load urgent priority.
 */
$priority_urgent = kyTicketPriority::getAll()
    ->filterByTitle("Urgent")
    ->first();

print 'Fetched: '.$priority_urgent;

/**
 * Create urgent ticket as the user created in previous step.
 */
$ticket = $user
    ->newTicket(
        $printers_department,
        "The printer on 4th floor in building B2 doesn't print at all. Fix it quickly, please.",
        "Printer not working (example)")
    ->setPriority($priority_urgent)
    ->create();

print 'Created: '.$ticket;

/**
 * Get ticket display id and print it.
 */
$ticket_display_id = $ticket->getDisplayId();
printf("The ticket was created and its ID is: %s\n", $ticket_display_id);

/**
 * Ticket processing.
 */

/**
 * Get the user that created the ticket.
 */
$user = $ticket->getUser();

/**
 * Find ticket status with title "In Progress".
 */
$status_in_progress = kyTicketStatus::getAll()
    ->filterByTitle("In Progress")
    ->first();

print 'Fetched: '.$status_in_progress;

/**
 * Find ticket status with title "Closed".
 */
$status_closed = kyTicketStatus::getAll()
    ->filterByTitle("Closed")
    ->first();

print 'Fetched: '.$status_closed;

/**
 * Assign the staff user created before.
 */
$ticket
    ->setOwnerStaff($staff_user)
    ->update();

print 'Updated ticket owner: '.$ticket;

/**
 * Add new post (staff user reply).
 */
$ticket_post = $ticket
    ->newPost($staff_user, "Did you switched the printer on?")
    ->create();

print 'Created ticket post: '.$ticket_post;

/**
 * Change ticket status.
 */
$ticket
    ->setStatus($status_in_progress)
    ->update();

print 'Updated ticket status: '.$ticket;

/**
 * Add new post (user reply).
 */
$user_reply_post = $ticket
    ->newPost($user, "Yes, of course! See attached photo of the printer.")
    ->create();

print 'Created ticket post: '.$user_reply_post;

/**
 * Add attachment to the post (for now using example image from Wikimedia Common).
 */
$ticket_post_attachment = $user_reply_post
    ->newAttachmentFromFile("http://upload.wikimedia.org/wikipedia/commons/0/0b/Canon_ir2270.jpg")
    ->create();

print 'Created ticket post attachment: '.$ticket_post_attachment;

/**
 * Add "note to myself".
 */
$ticket_note = $ticket->newNote($staff_user, "Power cable needs replacement.")
    ->create();

print 'Created ticket note: '.$ticket_note;

/**
 * Add new post (staff user reply).
 */
$staff_reply_post = $ticket
    ->newPost($staff_user, "I think I know what's wrong. It will be fixed within half an hour.")
    ->create();

print 'Created ticket post: '.$staff_reply_post;

/**
 * Change ticket status.
 */
$ticket
    ->setStatus($status_in_progress)
    ->update();

print 'Updated ticket status: '.$ticket;

/**
 * Add new post (user reply).
 */
$user_reply_post = $ticket
    ->newPost($user, "Thank you. It's working now.")
    ->create();

print 'Created ticket post: '.$user_reply_post;

/**
 * Close the ticket.
 */
$ticket
    ->setStatus($status_closed)
    ->update();

print 'Updated ticket status: '.$ticket;

/**
 * Search for open tickets in departments with (caseless) "printer" inside of title,
 * which were created by user with e-mail anno.ying@example.com.
 */
$tickets = kyTicket::getAll(
    kyDepartment::getAll()
        ->filterByTitle(array("~", "/printer/i")),
    kyTicketStatus::getAll()
        ->filterByTitle(array("!=", "Closed")),
    array(),
    kyUser::getAll()
        ->filterByEmail("anno.ying@example.com")
);

//print them
print "Searching tickets:\n".$tickets;

/**
 * Search for tickets with "power cable" text in contents of posts or notes.
 */
$tickets = kyTicket::search("power cable", array(kyTicket::SEARCH_CONTENTS, kyTicket::SEARCH_NOTES));

//print them
print "Searching tickets:\n".$tickets;

/**
 * Search for open and assigned tickets with no replies in all departments.
 * WARNING: Can be time consuming.
 */
$tickets = kyTicket::getAll(kyDepartment::getAll())
    ->filterByStatusId(kyTicketStatus::getAll()
        ->filterByTitle(array("!=", "Closed"))->collectId())
    ->filterByReplies(array('<=', 1))
    ->filterByOwnerStaffId(array("!=", null));

//print them
print "Searching tickets:\n".$tickets;

/**
 * Filtering, sorting and paging results.
 */

//print available filter methods for User objects
print "User available filter methods:\n";
print_r(kyUser::getAvailableFilterMethods());

//print available order methods for Staff objects
print "Staff available order methods:\n";
print_r(kyStaff::getAvailableOrderMethods());

//find the user with email someuser@example.com
$user = kyUser::getAll()->filterByEmail("someuser@example.com")->first();

//find ticket time tracks with billable time greater than 10 minutes and sort them ascending using time worked
$time_tracks = $ticket->getTimeTracks()->filterByTimeBillable(array(">", 10 * 60))->orderByTimeWorked();

//find department with title "General"
$general_department = kyDepartment::getAll()->filterByTitle("General")->first();

//find tickets in "General" department with word "help" in subject
$tickets = kyTicket::getAll($general_department->getId())->filterBySubject(array("~", "/help/i"));

//assuming 10 items per page, get second page from list of staff users ordered by fullname
$staff_page_2 = kyStaff::getAll()->orderByFullName()->getPage(2, 10);
