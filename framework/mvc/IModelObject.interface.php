<?php

namespace framework\mvc;

interface IModelObject {

    public function __construct();

    public function hydrate($datas = array());
}

?>
