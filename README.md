Validation Rule Generator
=============================

This Laravel 4 package will automatically generate laravel validation rules, based on your schema. It can generate rules for:

* All tables in the database
* One (given) table
* One (given) column from a (given) table

Pass in custom rules to override the automatically generated rules.

You can use the results from the ValidationRuleGenerator directly in a validator, or you can copy them from an Artisan command, and manually enter them into your models.



## Installation

Install the package via Composer. Edit your `composer.json` file to require `kalani/validation-rule-generator`.

``` json
"require": {
    "laravel/framework": "4.0.*",
    "kalani/validation-rule-generator": "dev-master"
}
```

Next, update Composer from the terminal:

    composer update

Finally, add the service provider to the providers array in `app\config\app.php`:

    'Kalani\ValidationRuleGenerator\ValidationRuleGeneratorServiceProvider',

There is already an alias set up for `ValidationRuleGenerator`, but if you would like to refer to it by a short, memorable name in your app, you can add another alias as follows:

    'Rules' => 'Kalani\ValidationRuleGenerator\Facades\ValidationRuleGenerator',

(We will use ValidationRuleGenerator and Rules interchangeably)



## Usage

#### Artisan Command

`php artisan make:validation` will generate rules for a given table or model. These rules will be printed in a format that you could copy directly to another php file.

On the command line, you can run the rule generator as follows:

    php artisan make:validation [--model="..."] [--table="..."] [--all]

One of these parameters is required:

    --all               Output table information for all tables in the database
    --table=table_name  Returns rules for the given table
    --model=ModelName   Returns rules for the given model (with overrides)

If you pass in a `--model` parameter, the rule generator will generate rules for the model's table, and will then merge it with the model's `$rules` array. In cases of a conflict, the `$rules` array will take precedence.


#### Pass directly into validator

Call `ValidationRuleGenerator::getRules($table|$model, $column, $rules, $id)`:

    * `$table`  The name of the table (or model object) for which to get rules
    * `$column` The name of the column
    * `$rules`  Custom rules (override automatically generated rules)
    * `$id`     Ignore unique rules for the given id

All of the parameters are optional. If you do not include any, the package will return an array of all rules in the database. If you include the $table, rules will be gathered from that table; $table and $column, rules will be gathered for the given table/column.

To validate a table:

    $valid = Validator::make(Input::all(), Rules::getRules($tableName));

To validate a table, ignoring a given id:

    $valid = Validator::make(Input::all(), Rules::getRules($tableName, null, null, $id));


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


