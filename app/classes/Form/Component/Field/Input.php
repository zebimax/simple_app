<?php

namespace Form\Component\Field;

class Input extends AbstractField
{
    const SEARCH_TYPE = 'search';
    const SUBMIT_TYPE = 'submit';
    const HIDDEN_TYPE = 'hidden';
    const TEXT_TYPE = 'text';

    protected $tag = 'input';
}