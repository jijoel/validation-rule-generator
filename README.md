This Laravel 4 package will automatically generate laravel validation rules, based on your schema. It can generate rules for:

    * All tables in the database
    * One (given) table
    * One (given) column from a (given) table

You can also pass in custom rules, which will override the automatically generated rules.



## Installation

Install the package via Composer. Edit your `composer.json` file to require `kalani/validation-rule-generator`.

    "require": {
        "laravel/framework": "4.0.*",
        "kalani/validation-rule-generator": "dev-master"
    }

Next, update Composer from the terminal:

    composer update

Finally, add the service provider to the providers array in `app\config\app.php`:

    'Kalani\ValidationRuleGenerator\ValidationRuleGeneratorServiceProvider',


## Usage

Call `ValidationRuleGenerator::getRules($table, $column, $rules, $id)`:

    * `$table`  The name of the table for which to get rules
    * `$column` The name of the column
    * `$rules`  Custom rules (override automatically generated rules)
    * `$id`     Ignore unique rules for the given id

All of the parameters are optional. If you do not include any, the package will return an array of all rules in the database. If you include the $table, rules will be gathered from that table; $table and $column, rules will be gathered for the given table/column.

To validate a table:

    $valid = Validator::make(Input::all(), ValidationRuleGenerator::getRules($tableName));

To validate a table, ignoring a given id:

    $valid = Validator::make(Input::all(), ValidationRuleGenerator::getRules($tableName, null, null, $id));


### Alternative Usage

To get all of the rules for a table, in your controller: 

    $rules = ValidationRuleGenerator::getTableRules($model->getTable(), array($custom_rules));
    $validation = Validator::make(Input::all(), $rules);

You can also generate rules to ignore the current record id:

    $rules = ValidationRuleGenerator::getUniqueRules($rules, $id);

If you'd like an array of all validation rules for all tables in your database:

    $rules = ValidationRuleGenerator::getAllRules();

If you'd like validation rules for one column:

    $rules = ValidationRuleGenerator::getColumnRules($table, $column);


Tests
------------
Currently, I am not sure how to get testing to work for packages. Several tests were written before I changed this class to a package; they're included here for reference, but they don't currently work.

