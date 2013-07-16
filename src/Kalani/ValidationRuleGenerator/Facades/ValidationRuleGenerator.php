<?php 

namespace Kalani\ValidationRuleGenerator\Facades;
 
use Illuminate\Support\Facades\Facade;
 
class ValidationRuleGenerator extends Facade 
{
 
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor() { return 'validation-rule-generator'; }
 
}