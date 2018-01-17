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

