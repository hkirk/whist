/**
 * Game.java
 * Copyright 2014 Busywait.org. All rights reserved.
 */
package controllers

import javax.inject.Inject

import model.service.GameService
import model.{GameForm, Location}
import play.api.data.Form
import play.api.data.Forms._
import play.api.db.Database
import play.api.mvc.{Action, Controller}
import play.api.i18n.{I18nSupport, MessagesApi}

class GameController @Inject()(
                        val db: Database,
                        val messagesApi: MessagesApi,
                        val gameService: GameService
                      ) extends Controller with I18nSupport {

  val createGameFrom = Form(
    mapping(
      "location" -> nonEmptyText,
      "description" -> text,
      "rule" -> text,
      "attachment" -> text
    )(GameForm.apply)(GameForm.unapply)
  )

  def game(id: Long) = Action { implicit r =>
    db.withConnection {
      implicit c =>
        Ok(views.html.game.game(gameService.getGameWithPlayers(id)))
    }
  }

  def newGame() = Action { implicit r =>
    db.withConnection {
      implicit c =>
        val locationsTuples = Location.createLocationSelectTuple(Location.getLocations())
        Ok(views.html.game.newGame(createGameFrom, locationsTuples, Seq()))
    }
  }

  def createGame() = TODO
}
