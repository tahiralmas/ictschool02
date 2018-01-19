School managment system REST APIs Guide
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
Users
-----
### GET users

* __Response__  
an array of teachers


### GET users/{user_id}
Read / view complete user data

* __Parameters__  
Replace {user_id} in url with valid user_id

* __Response__
an array of specific user

```json
{
  "user": {
    "id": "user_id",
    "firstname": "firstname.",
    "lastname": "lastname",
    "desc": "description",
    "login": "login" username,
    "email": "email",
    "group": "group" Admin,Student,Teacher
  }
}

```

### PUT users/{user_id}
Update an existing user

* __Parameters__  
Replace {user_id} in url with valid user_id, fields require modifications will be

```json
{
"firstname": "__String__",
"lastname": "__Strring__",
"password": "__String__",

}
```
* __Response__  
Return updated user data as an json

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




Teachers
---------
### GET teachers

* __Response__  
an array of teachers


### GET teacher/{teacher_id}
Read / view complete teacher data

* __Parameters__  
Replace {teacher_id} in url with valid teacher_id

* __Response__
an array of specific teacher

```json
{
      "id"              : "teacher_id",
      "firstName"       : "firstName",
      "lastName"        : "lastName",
      "gender"          : "gender",
      "religion"        : "religion",
      "bloodgroup"      : "bloodgroup",
      "nationality"     : "nationality",
      "dob"             : "dob",
      "photo"           : "photo name",
      "phone"           : "phone",
      "email"           : "email",
      "fatherName"      : "fatherName",
      "fatherCellNo"    : "fatherCellNo",
      "presentAddress"  : "presentAddress",
      "parmanentAddress": "parmanentAddress",
      "created_at"      : "created_at",
      "updated_at"      : "updated_at"
      }
```

### PUT teachers/{teacher_id}
Update an existing teacher

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

* __Response__

```json
    {
      "id"         : "class_id",
      "code"       : "class code",
      "name"       : "class name",
      "description": "class description",
      "created_at" : "created_at",
      "updated_at" : "updated_at"
    },
```

### GET classes/{class_id}/sections
Read / view complete classes data

* __Parameters__  
Replace {class_id} in url with valid class_id

* __Response__  
Return  sections list assign to class as an json form

```json
{
  "class_section": [
    {
      "name"       : "section name",
      "description": "section description"
    }
  ]
}
```

### POST classes/{class_id}/notifications
create call request class wise / dial 

* __Parameters__  
  * A json encoded associative array containing key and value pairs based on following fields
```json
{
    "name": "__String__",
    "recording"  : "__media file__, choise wav audio file",
    "description":"__Optional__",
}
```
* __Response__  
__notification_id__ of recently created notification record

Sections
---------
### GET sections

also search by parameter(sections?class)    e.g class=class_id


* __Response__  
an array of sections

### GET sections/{section_id}
Read / view complete sections data

* __Parameters__  
Replace {section_id} in url with valid section_id


* __Response__

```json
{
    "id"         : "section id",
    "name"       : "section name",
    "description": "section description",
    "class_code" : "class code"
}
```

### GET sections/{section_id}/subjects
Read / view complete section subjects data

* __Parameters__  
Replace {section_id} in url with valid section_id

* __Response__  
Return  section subjects list assign to class or section as an json form

```json
{
  "subjects": [
    {
      "code"    : "subject code",
      "name"    : "subject name",
      "type"    : "type , like Comprehensive,core,electives,
      "class"   : "class code",
      "stdgroup": "student group , like science ,arts etc"
    }
  ]
}
```


### GET sections/{section_id}/students
Read / view complete section student data

* __Parameters__  
Replace {section_id} in url with valid section_id

* __Response__  
Return  section students list assign to class or section as an json form

```json
{
  "student": [
    {
      "id"               : "student id",
      "regiNo"           : "student registration",
      "rollNo"           : "student roll no",
      "session"          : "session , like year which year student enter",
      "class"            : "class code",
      "group"            : "group ,like science ,arts etc",
      "section"          : "section id",
      "shift"            : "shift ,like Morning evening",
      "firstName"        : "student firstName",
      "middleName"       : "student middleName",
      "lastName"         : "student lastName",
      "gender"           : "gender ",
      "religion"         : "religion",
      "bloodgroup"       : "bloodgroup",
      "nationality"      : "nationality",
      "dob"              : "date of brith",
      "photo"            : "photo name ",
      "extraActivity"    : "extraActivity",
      "remarks"          : "remarks",
      "fatherName"       : "fatherName",
      "fatherCellNo"     : "fatherCellNo",
      "motherName"       : "motherName",
      "motherCellNo"     : "motherCellNo",
      "localGuardian"    : "localGuardian",
      "localGuardianCell": "localGuardianCell",
      "presentAddress"   : "presentAddress",
      "parmanentAddress" : "parmanentAddress",
      "isActive"         : "isActive , Yes or No",
      "created_at"       : "created_at",
      "updated_at"       : "updated_at"
    },

  ]
}

```
### GET sections/{section_id}/teachers
Read / view complete section teacher data

* __Parameters__  
Replace {teacher_id} in url with valid teacher_id

* __Response__  
Return  section teachers list assign to class or section as an json form

```json
{
  "teacher": [
    {
      "id"            : "teacher id",
      "firstName"     : "teacher firstName",
      "lastName"      : "teacher lastName",
      "fatherName"    : "fatherName",
      "fatherCellNo"  : "fatherCellNo",
      "presentAddress": "presentAddress",
      "Subject"       : "subject Name"
    },
  ]
}
```
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

also search by parameter(studentsregiNo&class) or class,section,name,group   e.g class=class_id,section=section_id

* __Response__  
an array of all register students

### GET students/{student_id}
Read / view complete students data

* __Parameters__  
Replace {student_id} in url with valid student_id

* __Response__

```json
{
  "studnet": {
    "id"               : "student id",
    "regiNo"           : "student registration",
    "rollNo"           : "student roll no",
    "firstName"        : "student firstName",
    "middleName"       : "student middleName",
    "lastName"         : "student lastName",
    "fatherName"       : "fatherName",
    "motherName"       : "motherName",
    "fatherCellNo"     : "fatherCellNo",
    "motherCellNo"     : "motherCellNo",
    "localGuardianCell": "localGuardianCell",
    "class"            : "class",
    "section"          : "section",
      "group"          : "group ,like science ,arts etc",
     "presentAddress"  : "presentAddress",
     "gender"          : "gender ",
      "religion"       : "religion",
  }
}
```

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

* __Response__

```json
{
  "exam"     : {
    "type"   : "exam type like class test final term",
    "class"  : "class name",
    "section": "section name"
  }
}
```

Result
-------
### GET results

also search by parameter(results?regiNo&class) or class,section,name,exam,subject   e.g class=class_id,section=section_id,exam=exam_id,subject=subject_code

* __Response__  
an array of results

### GET results/{result_id}
Read / view complete result data

* __Parameters__  
Replace {result_id} in url with valid result_id

* __Response__

```json
{
  "result": {
    "id"          : "result id",
    "regiNo"      : "student registration number",
    "rollNo"      : "student roll number",
    "firstName"   : "student firstName",
    "lastName"    : "student lastName",
    "class"       : "class name",
    "section"     : "section name",
    "subject"     : "subject name",
    "written"     : "marke",
    "mcq"         : "marks",
    "practical"   : "marks",
    "ca"          : "marks",
    "total"       : "marks",
    "grade"       : "grade",
    "point"       : "point",
    "Absent"      : "No ,Yes"
  }
}

```

### POST results
Add result
* __Parameters__  

  * A json encoded associative array containing key and value pairs based on following fields

```json
{
    "class_id"    : "__String__",
    "section_id"  : "__String__, ",
    "session"     :"__String__",
    "regiNo"      : "__String__",
    "exam_id"     : "__String__, ",
    "subject_code":"__String__",
    "written"     : "__String__",
    "mcq"         : "__String__, ",
    "practical"   :"__String__",
    "ca"          : "__String__",
    "absent"      : "__String__, Yes Or No",
    
}
```
* __Response__  
__Result_id__ of recently created result record

### PUT results
Update an existing result



* __Parameters__ 

  * A json encoded associative array containing key and value pairs based on following fields

```json
{
    "class_id"     : "__String__",
    "section_id"   : "__String__, ",
    "session"      :"__String__",
    "regiNo"       : "__String__",
    "exam_id"      : "__String__, ",
    "subject_code" :"__String__",
    "written"      : "__String__",
    "mcq"          : "__String__, ",
    "practical"    :"__String__",
    "ca"           : "__String__",
    "absent"       : "__String__, Yes Or No",
    
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
Read / view complete attendances data and also search by parameter(attendaces?regiNo&date) or class,section,name date formate:'year-month-date'


* __Response__
class and section in Json form

### POST api/attendances
Create new Attendence

* __Parameters__
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "regiNo" : "__String__", Student registration number
    "date"   : "__String__",Date Formate date-month-year
    "status" :"__String__" Present,Absent
}
```
* __Response__
students attendance save Succesfully.

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
    "name"       : "__String__",
    "recording"  : "__media file__, choise wav audio file",
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





