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
--------------
* POST authenticate
* ~~POST authenticate/cancel~~

Admin / Staff / Students
------------------------
* ~~GET users~~
* POST users
* GET users/{user_id}
* PUT users/{user_id}
* ~~DELETE users/{user_id}~~

Teachers
--------
* GET teachers
* ~~POST teachers~~
* GET teachers/{teacher_id}
* PUT teachers/{teacher_id}
* ~~DELETE teachers/{teacher_id}~~
* ~~GET teachers/{teacher_id}/classes~~
* GET teachers/{teacher_id}/sections
* ~~GET teachers/{teacher_id}/students~~
* GET teachers/{teacher_id}/subjects

Classes
-------
* GET classes
* GET classes/{class_id}
* PUT classes/{class_id}
* GET classes/{class_id}/sections
* ~~GET classes/{class_id}/subjects~~
* ~~GET classes/{class_id}/teachers~~
* ~~GET classes/{class_id}/exams~~
* POST classes/{class_id}/notifications

Sections
--------
* GET sections
* GET sections/{section_id}
* ~~PUT sections/{section_id}~~
* GET sections/{section_id}/subjects
* GET sections/{section_id}/students
* GET sections/{section_id}/teachers ; i.e associated teachers
* ~~GET sections/{section_id}/exams~~
* POST sections/{section_id}/notifications

Students
--------
* GET students
* GET students/{student_id}
* PUT students/{student_id}
* ~~GET students/{student_id}/subjects~~
* ~~GET students/{student_id}/exams~~
* ~~GET students/{student_id}/teachers~~
* POST students/{student_id}/notifications

Subjects
--------
* ~~GET subjects~~
* ~~POST subjects~~
* ~~GET subjects/{subject_id}~~
* ~~PUT subjects/{subject_id}~~
* ~~DELETE subjects/{subject_id}~~

Exams
-----
* GET exams
* ~~POST exams~~
* GET exams/{exam_id}
* ~~PUT exams/{exam_id}~~
* ~~DELETE exams/{exam_id}~~

Results
-------
* GET results
* POST results
* GET results/{result_id}
* PUT results/{result_id}
* DELETE results/{result_id}

Notifications
-------------
* GET notifications
* POST notifications
* GET notifications/{notification_id}
* PUT notifications/{notification_id}
* DELETE notifications/{notification_id}

Attendence
----------
* GET attendances
* POST attendances
* GET attendances/{attendance_id}
* PUT attendances/{attendance_id}
* DELETE attendances/{attendance_id}

