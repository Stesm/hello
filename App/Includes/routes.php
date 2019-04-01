<?php

return [
    // Site
    ['site-index', '/', 'AppController@index'],

    // Admin
    ['admin-index', '/manage/', 'Admin\Index@index'],
    ['admin-user', '/manage/users/?#action#?/#user_id#?/?', 'Admin\Users@edit'],
];
