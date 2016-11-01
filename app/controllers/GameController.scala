/**
 * Game.java
 * Copyright 2014 Busywait.org. All rights reserved.
 */
package controllers

import javax.inject.Inject

import play.api.mvc.{Action, Controller}
import model.Game
import play.api.i18n.{I18nSupport, MessagesApi}

class GameController @Inject()(
                        val messagesApi: MessagesApi
                      ) extends Controller with I18nSupport {

  def game(id: Long) = Action {
    Ok(views.html.game.game(Game.getGameWithPlayers(id)))
  }

  def newGame() = TODO

  def createGame() = TODO
}
