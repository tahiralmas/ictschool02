ICT School Management System
=============================
Features
--------
* Users
* Classes
* Section
* Student
* Teacher
* Marks
* Result
* Fee /Dues
* Attendance
* Notification

### User
---------

Create unlimited users with various roles such as Admin, Teachers, Students. There is no limit to number of users.

### Classes and Section Management
------------------------------------

Class add update and delete Same as Section
Manage as usual classes and create as many sections required, assign teachers to sections and add educational subjects to different classes

### Students
-------------

 Student Create upade and delete Also student promoted to a new class and to a new session.

### Teachers
-------------

Teacher Create update and delete.Also set teacher timetable assaign class,section and suject specific time and days.

### Marks and Result
---------------------

Add Marks subject,class and section wise to specific session student .Create Marks, Define your own Marking Grade, Manage Subject-wise Marks for exams or generate tabulation sheets in Result portion to clicking generate result. 

### Fees
---------

Fee structure module allows you to manage existing or define and create many new different fees.
Fee structure also empowers you to define variable fees for different classes , along this you get fee type, where you can define if its a monthly, other! 

### Attendance
--------------

Create Attendance Class,section And session wise and send notification to absent students parent. this is not manual.
easy to use interface to enable class teachers take and track daily attendance of students in their classes and sections

### Notification
----------------

With built-in Notifications module, you can easily create notifications and can send it via Voice Call to different groups of Users, i-e
To all Teachers, To all Student Parents or to Student of any one specific classes and sections.


API List
========
A complete list of available APIs only for overview and analysis, for full documentation please see ApiGuide.md

Authentication
---------------
* POST authenticate

Users (Admin,staff,student)
----------------------------
* POST users
* GET users
* PUT users/{user_id}
* GET users/{user_id}

Teachers
---------
* GET teachers
* GET teachers/{teacher_id}
* GET teachers/{teacher_id}/section
* GET teachers/{teacher_id}/subjeccts

Classes
-------
* GET classes
* GET classes/{class_id}
* PUT classes/{class_id}
* GET classes/{class_id}/sections
* POST classes/{class_id}/notifications

sections
---------
* GET sections
* GET sections/{section_id}
* GET sections/{section_id}/subjects
* GET sections/{section_id}/student
* GET sections/{section_id}/teachers
* POST sections/{section_id}/notifications

Students
--------
* GET students
* GET students/{student_id}
* PUT students/{student_id}
* POST students/{student_id}/notifications


Exams
--------
* GET exams
* GET exams/{exam_id}

Results
--------
* GET results
* GET results/{result_id}
* POST results
* PUT results/{result_id}
* DELETE results/{result_id}

Notification
--------
* GET notification
* GET notification/{notification_id}
* POST notification
* PUT notification/{notification_id}
* DELETE notification/{notification_id}

Attendence
----------
* GET attendances
* POST attendances
* GET attendances/{attendance_id}
* PUT attendances/{attendance_id}
* DELETE attendances/{attendance_id}

