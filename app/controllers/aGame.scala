/**
 * Game.java
 * Copyright 2014 Busywait.org. All rights reserved.
 */
package controllers

import play.api.mvc.{Action, Controller}
import controllers.model.Game

object AGame extends Controller {

  def aGame(id: Long) = Action {
    Ok(views.html.aGame(Game.getGameWithPlayers(id)))
  }

}
