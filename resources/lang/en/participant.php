<?php

return [
    'singular' => 'Participant',
    'plural' => 'Participants',

    'section' => [
        'bio_heading' => 'Biodata',
        'info_heading' => 'Information',
    ],

    'notification' => [
        'import_title' => 'Import success',
        'import_body' => ':value Athlete successfully registered',

        'verified_title' => 'Verification success',
        'verified_body' => ':athlete successfully verified',
        'bulk_verified_title' => 'Bulk verification success',
        'bulk_verified_body' => ':number athletes successfully verified',

        'disqualified_title' => 'Disqualification success',
        'disqualified_body' => ':athlete successfully disqualified',
        'bulk_disqualified_title' => 'Bulk disqualification success',
        'bulk_disqualified_body' => ':number athletes successfully disqualified',

        'deregistered_title' => 'Deregistration success',
        'deregistered_body' => ':athlete successfully deregistered',
        'bulk_deregistered_title' => 'Bulk deregistration success',
        'bulk_deregistered_body' => ':number athletes successfully disqualified',
    ],

    'action' => [
        'import' => 'Import participants',
        'verify' => 'Verify',
        'bulk_verify' => 'Verify in bulk',
        'disqualify' => 'Disqualify',
        'bulk_disqualify' => 'Disqualify in bulk',
        'deregister' => 'Deregister',
        'bulk_deregister' => 'Deregister in bulk',
        'upload_participant' => 'Upload participants',
    ],

    'role' => [
        'athlete' => 'Athlete',
        'manager' => 'Manager',
    ],

    'participation' => [
        'verification' => 'Verification',
        'verified' => 'Verified',
        'unverified' => 'Unverified',
        'registration' => 'Registration',
        'registered' => 'Registered',
        'disqualification' => 'Disqualification',
        'disqualified' => 'Disqualified',
    ],

    'field' => [
        'name' => 'Nama',
        'classification' => 'Classification',
        'role' => 'Role',
        'gender' => 'Gender',
        'draw_number' => 'Draw Number',
    ],
];
