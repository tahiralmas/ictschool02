School managment system attence REST APIs Guide
===============================================

Overview
--------
* __API Access__ : Any valid combination of username and password created in usr table.
* __POST Method__: Any data submitted to POST method based APIs must be encoded as json.
* __GET Method__: user can use  GET methods for  APIs.
* __List APIs__: All list APIs support optional search filter, to search Attendence and Student need to use search parameters in url as query string using key value pair.

Attendence
----------
### POST api/login


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
### GET api/attendance
Read / view complete class and section data

* __Response__
class and section in Json form

### POST api/attendance-create
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

### POST api/student-classwise
list of student 

* __Parameters__
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "class": "__String__",
    "section": "__String__",
    "shift": "__String__, Day,morning",
    "sessions": "__String__", year
}
```
* __Response__
list of students.


### POST api/attendance-view
list of Attendence date wise 

* __Parameters__
A json encoded associative array containing key and value pairs based on following fields
```json
{
    "class": "__String__",
    "section": "__String__",
    "shift": "__String__, Day,morning",
    "sessions": "__String__", year,
    "date": "__String__",Date
}
```
* __Response__
list of students present or absent.

### GET api/details
Read / view complete login user  data

* __Response__
Login user data in Json form



