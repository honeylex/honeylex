<?php

namespace Foh\SystemAccount\User\Projection\Standard;

use Foh\SystemAccount\User\Projection\Standard\Base\User as BaseUser;

/**
 * This class may be used to customize the behaviour of the
 * 'User' entities and has built-in validation and
 * change tracking.
 *
 * Defines the (normalized) strucuture of a default user projection.
 *
 * To get all changes since the last call to 'markClean()' use
 * the 'getChanges()' method. Call 'isClean()' to get a summary.
 *
 * To check if the entity is in a coherent state according
 * to the set attributes use the 'isValid()' method and check the
 * specific validation results via 'getValidationResults()'.
 * Every validation incident above NOTICE level marks this
 * entity as invalid.
 *
 * There is no default entity or type wide validation atm,
 * but this may be implemented via overriding the 'isValid()'
 * method or by registering and implementing change event listeners
 * via the '(add|remove)EntityChangedListener()' methods.
 *
 * For more information and hooks have a look at the base classes.
 */
class User extends BaseUser
{

}
