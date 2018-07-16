<?php

namespace Laravelladder\Core\ServiceProviders;

use Laravelladder\Core\Validations\Rule;
use Laravelladder\Core\Validations\Validator;
use Illuminate\Validation\ValidationServiceProvider as ServiceProvider;
use Illuminate\Validation\Factory;
class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Register the validation factory.
     *
     * @return void
     */
    protected function registerValidationFactory()
    {
        $this->app->singleton('validator', function ($app) {
            $validator = new Factory($app['translator'], $app);

            $validator->resolver(function($translator, $data, $rules, $messages, $customAttributes) use ($validator) {
                return new Validator($translator, $data, $rules, $messages, $customAttributes);
            });

            // The validation presence verifier is responsible for determining the existence
            // of values in a given data collection, typically a relational database or
            // other persistent data stores. And it is used to check for uniqueness.
            if (isset($app['validation.presence'])) {
                $validator->setPresenceVerifier($app['validation.presence']);
            }

            return $validator;
        });

        Rule::registerCustomValidator();
    }
}
