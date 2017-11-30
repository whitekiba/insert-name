<?php namespace Fluxnet\Interfaces;

interface Controller {
    public function render();
    public function action($action);
    public function post_action($action);
    public function ajax($action);
    public function post($action);
    public function get($action);
    public function setMap($map);
    public function setRouter($router);
    public function init();
}
