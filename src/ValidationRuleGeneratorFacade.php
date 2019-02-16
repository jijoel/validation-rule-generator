<?php

namespace Jijoel\ValidationRuleGenerator;

use Illuminate\Support\Facades\Facade;

class ValidationRuleGeneratorFacade extends Facade
{

  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor() { return 'validation-rule-generator'; }

}
