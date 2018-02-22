<?php
namespace App;
class Schedule extends \Eloquent {
	protected $table = 'cronschedule';
	 //public $timestamps = r;
	protected $fillable = ['date','time'];
}
