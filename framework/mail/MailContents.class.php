<?php

namespace framework\mail;

use framework\utility\Validate;

class MailContents {

    private $mailName;
    private $mailVars = array();

    public function __construct($mailTpl) {
        if (!file_exists($mailTpl))
            throw new \Exception('Mail data file error');

        $this->mailName = $mailTpl;
    }

    public function addVar($varName, $varValue) {
        if (!Validate::isVariableName($varName))
            throw new \Exception('Var name must be a validate variable name');

        //On verifie que la var n'a pas déja été définie
        for ($i = 0; $i < count($this->mailVars); $i++) {
            if ($varName == $this->mailVars[$i]['varName'])
                throw new \Exception('Var : "' . $varName . '" is already defined');
        }
        //On assimile les valeurs
        $this->mailVars[] = array(
            'varName' => $varName,
            'varValue' => $varValue);

        return $this;
    }

    public function getMailContents() {
        if (!$this->mailName)
            throw new \Exception('Mail name must be setted');

        if ($this->mailVars) {
            for ($i = 0; $i < count($this->mailVars); $i++)
                ${$this->mailVars[$i]['varName']} = $this->mailVars[$i]['varValue'];
        }
        ob_start();
        require $this->mailName;
        $mailContents = ob_get_contents();
        ob_end_clean();
        return $mailContents;
    }

}
?>