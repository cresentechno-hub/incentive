<?php

class HelpController
{
    public function index(): void
    {
        Auth::require('Admin', 'Manager', 'Agent');
        render('help/index', []);
    }
}
