<?php


class usergrade {
    public $grade = "N";
    public $assignid = 0;
    public $user;
    public $modulegrade;
    private $grades = array();

    public function addgrade($grade, $maxgrade) {
        $this->grades[]['grade'] = $grade;
        $this->grades[]['maxgrade'] = $maxgrade;
        if ($grade <= $maxgrade) {
            $this->modulegrade = $grade;
        }
    }

    public function usergrade($user) {
        $this->user = $user;
        $this->modulegrade = 0;
    }

}
