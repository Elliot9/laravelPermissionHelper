### Install

```php
  composer require elliot9/laravel-permission-helper
```

### Configuration

add all of the following classes to your config/app.php service providers list.
```php
  Elliot9\laravelPermissionHelper\PermissionHelperServiceProvider::class
```

and add this below the aliases
```php
  'PermissionHelper' => Elliot9\laravelPermissionHelper\PermissionHelperFacade::class
```

Publish the storage configuration file and migrateion.
```php
  php artisan vendor:publish --provider="Elliot9\laravelPermissionHelper\PermissionHelperServiceProvider" --tag="migrations"
  php artisan vendor:publish --provider="Elliot9\laravelPermissionHelper\PermissionHelperServiceProvider" --tag="config"
```

Running Migrations
```php
  php artisan migrate
```


### Setting

set all the Authenticatable class at config like ->
```php
  return [
      'PermissionSetting' => [
          'types' => [
              'User' =>   \App\User::class,
              'Admin' => \App\Admin::class,
          ]
      ]
  ];
```


### Usage

```php
//Binding Model
$user = PermissionHelper::SetInstance($user);

// Adding role to a user
$user->SetRole('admin|writer|driver|...');

// Adding permissions via a role
$user->SetRolePermission('update time|edit articles|delete papers|...');

// Get user's all roles
$user->GetRole();

// Get user's all permissions
$user->GetPermission();

// Remove user's role
$user->RemoveRole('admin|driver');

// Remove permissions via a role
$user->RemoveRolePermission('update time');


// Create new role
$PermissionHelper = PermissionHelper::SetInstance(User::class);
$PermissionHelper->CreateRole('author|officer');

// Create new permissions
$PermissionHelper = PermissionHelper::SetInstance(User::class);
$PermissionHelper->CreatePermission('edit paper|delete tickets');


// Delete role
$PermissionHelper->DeleteRole('author');


// Delete permissions
$PermissionHelper->DeletePermission('edit paper|delete tickets');


// Check had permission
$user->HasPermission('edit paper|delete tickets');


// Using in Blade
@HasPermission
@HasRole
@endHas

// Using in middleware
$this->middleware('PermissionCheck:edit papers');

```


