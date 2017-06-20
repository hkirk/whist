package controllers

import javax.inject.Inject

import play.api.mvc._
import model.Game
import play.api.db.Database
import play.api.i18n.{I18nSupport, MessagesApi}

class Application @Inject() (
                              val db: Database,
                              val messagesApi: MessagesApi
                            ) extends Controller with I18nSupport{

  def index(message: Option[String]) = Action {
    db.withConnection {
      implicit c =>
        val games = Game.getGames
        Ok(views.html.index(games, message))
    }
  }

}
