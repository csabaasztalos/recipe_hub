<?php

interface IPageBase {
    public function GetTemplate(): Template;

    public function Run(array $pageData): void;
}