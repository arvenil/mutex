<?php
/**
 * @author kamil.dziedzic
 */
// TODO: check include path
ini_set(
    'include_path',
    join(
        PATH_SEPARATOR,
        array(
            ini_get('include_path'),
            __DIR__ . DIRECTORY_SEPARATOR . '../src',
            __DIR__ . DIRECTORY_SEPARATOR . '../vendor',
        )
    )
);
