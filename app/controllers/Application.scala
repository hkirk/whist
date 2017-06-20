package controllers

import javax.inject.Inject

import play.api.mvc._
import model.Game
import model.service.GameService
import play.api.db.Database
import play.api.i18n.{I18nSupport, MessagesApi}

class Application @Inject() (
                              val db: Database,
                              val messagesApi: MessagesApi,
                              val gameService: GameService
                            ) extends Controller with I18nSupport{

  def index(message: Option[String]) = Action {
    db.withConnection {
      implicit c =>
        val games = gameService.getGames
        Ok(views.html.index(games, message))
    }
  }

}
