<?php

namespace NewsApi;

/**
 * Contract for News API wrappers.
 *
 * @author Rodrigo
 */
interface InterfaceApi
{
    public function getData(): mixed;
}
