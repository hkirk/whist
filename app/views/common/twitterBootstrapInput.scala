/**
 * twitterBootstrapInput.java
 * Copyright 2013 Busywait.org. All rights reserved.
 */
package views.common

import views.html.helper.FieldConstructor
import views.html.common.twitterBootstrapInput

object TwitterBootstrapInput {

  implicit val myFields = FieldConstructor(twitterBootstrapInput.f)

}
