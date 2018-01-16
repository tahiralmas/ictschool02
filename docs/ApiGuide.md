School managment system attence REST APIs Guide
===============================================

Overview
--------
* __API Access__ : Any valid combination of username and password created in usr table.
* __POST Method__: Any data submitted to POST method based APIs must be encoded as json.
* __GET Method__: user can use  GET methods for  APIs.
* __List APIs__: All list APIs support optional search filter, to search Attendence and Student need to use search parameters in url as query string using key value pair.

Authentication
---------------
### POST api/authenticate


* __Parameters__
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "email": "__String__",
    "password": "__String__",
}
```
* __Response__
give token 

## Header Parameter
```json
{
     'Accept' => 'application/json',
     'Authorization' => 'Bearer '.$accessToken,
     'X-Requested-With' => 'XMLHttpRequest'
                         
}
```
Teachers
---------
### GET teachers

* __Response__  
an array of teachers

### GET teacher/{teacher_id}
Read / view complete teacher data

* __Parameters__  
Replace {teacher_id} in url with valid teacher_id

### PUT teachers/{teacher_id}
Update an existing contact

* __Parameters__  
Replace {teacher_id} in url with valid teacher_id, fields require modifications will be POSTed in same way as `teachers`

* __Response__  
Return updated teacher data as an json

### GET teacher/{teacher_id}/sections
Read / view complete teacher data

* __Parameters__  
Replace {teacher_id} in url with valid teacher_id

### GET teacher/{teacher_id}/subjects
Read / view complete teacher data

* __Parameters__  
Replace {teacher_id} in url with valid teacher_id

* __Response__  
Return  teacher data with subjects who's assign teacher as an json form

Classes
-------
### GET classes

* __Response__  
an array of classes

### GET classes/{class_id}
Read / view complete class data

* __Parameters__  
Replace {class_id} in url with valid class_id

### GET classes/{class_id}/sections
Read / view complete classes data

* __Parameters__  
Replace {class_id} in url with valid class_id

* __Response__  
Return  sections list assign to class as an json form

### POST classes/{class_id}/notifications
create call request class wise / dial 

* __Parameters__  
  * A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "recording": "__media file__, choise wav audio file",
    "description":"__Optional__",
}
```
* __Response__  
__notification_id__ of recently created notification record

Sections
---------
### GET sections

* __Response__  
an array of sections

### GET sections/{section_id}
Read / view complete sections data

* __Parameters__  
Replace {section_id} in url with valid section_id

### GET sections/{section_id}/subjects
Read / view complete section subjects data

* __Parameters__  
Replace {section_id} in url with valid section_id

* __Response__  
Return  section subjects list assign to class or section as an json form


### GET sections/{section_id}/students
Read / view complete section student data

* __Parameters__  
Replace {section_id} in url with valid section_id

* __Response__  
Return  section students list assign to class or section as an json form

### POST sections/{section_id}/notifications
create call request section wise / dial 

* __Parameters__  
  * A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "recording": "__media file__, choise wav audio file",
    "description":"__Optional__",
}
```
* __Response__  
__notification_id__ of recently created notification record

Student
---------
### GET students

* __Response__  
an array of all register students

### GET students/{student_id}
Read / view complete students data

* __Parameters__  
Replace {student_id} in url with valid student_id

### GET students/{student_id}/subjects
Read / view complete student subjects data

* __Parameters__  
Replace {student_id} in url with valid student_id

* __Response__  
Return   subjects list of student as an json form

### POST students/{student_id}/notifications
create call request section wise / dial 

* __Parameters__  
  * A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "recording": "__media file__, choise wav audio file",
    "description":"__Optional__",
}
```
also Replace {student_id} in url with valid student_id
* __Response__  
__notification_id__ of recently created notification record


Exam
-----
### GET exams

* __Response__  
an array of exams

### GET exams/{exam_id}
Read / view complete exam data

* __Parameters__  
Replace {exam_id} in url with valid exam_id

Result
-------
### GET results

* __Response__  
an array of results

### GET results/{result_id}
Read / view complete result data

* __Parameters__  
Replace {result_id} in url with valid result_id

### POST results
Add result
* __Parameters__  
  * A json encoded associative array containing key and value pairs based on following fields
```json
{
    "class_id": "__String__",
    "section_id": "__String__, ",
    "session":"__String__",
    "regiNo": "__String__",
    "exam_id": "__String__, ",
    "subject_code":"__String__",
    "written": "__String__",
    "mcq": "__String__, ",
    "practical":"__String__",
    "ca": "__String__",
    "absent": "__String__, Yes Or No",
    
}
```
* __Response__  
__Result_id__ of recently created result record

### PUT results
Add result
* __Parameters__  
  * A json encoded associative array containing key and value pairs based on following fields
```json
{
    "class_id": "__String__",
    "section_id": "__String__, ",
    "session":"__String__",
    "regiNo": "__String__",
    "exam_id": "__String__, ",
    "subject_code":"__String__",
    "written": "__String__",
    "mcq": "__String__, ",
    "practical":"__String__",
    "ca": "__String__",
    "absent": "__String__, Yes Or No",
    
}
```
* __Response__  
Return updated result data as an json

### DELETE results

* __Response__  
Success Message



Attendance
----------
### GET api/attendances
Read / view complete attendances data

* __Response__
class and section in Json form

### POST api/attendances
Create new Attendence

* __Parameters__
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "class": "__String__",
    "section": "__String__",
    "shift": "__String__, Day,morning",
    "sessions": "__String__", year
    "regiNo": "__String__", Student registration number
    "date": "__String__",Date
}
```
* __Response__
students attendance save Succesfully.


### POST api/attendances/{attendance_id}
Read / view complete attendance data

* __Parameters__
Replace {attendance_id} in url with valid attendance_id

### PUT attendances/{attendance_id}
Update an existing attendance

* __Parameters__  
Replace {attendance_id} in url with valid attendance_id, fields require modifications will be POSTed in same way as `attendances`

* __Response__  
Return updated attendance data as an  json


Notification
---------
### GET notications

* __Response__  
an array of all register students

### GET notications/{notification_id}
Read / view complete notications data

* __Parameters__  
Replace {notification_id} in url with valid notification_id

### POST notifications
create notification 

* __Parameters__  
  * A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "recording": "__media file__, choise wav audio file",
    "description":"__Optional__",
}
```
* __Response__  
__notification_id__ of recently created notification record

### PUT notifications/{notification_id}
Update an existing notification

* __Parameters__  
Replace {notification_id} in url with valid notification_id, fields require modifications will be POSTed in same way as `notification`

* __Response__  
Return updated notification data as an  json





