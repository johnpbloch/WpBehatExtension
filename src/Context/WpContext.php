<?php

namespace JPB\WpBehatExtension\Context;

use Behat\MinkExtension\Context\RawMinkContext;
use JPB\WpBehatExtension\Context\Traits\UserContext;

class WpContext extends RawMinkContext {

	use UserContext;

}
