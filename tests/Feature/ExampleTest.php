<?php

it('redirects visitors hitting the root to the client hub', function () {
    $response = $this->get('/');

    $response->assertRedirect('/client-hub');
});
