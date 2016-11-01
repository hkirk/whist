package controllers

import javax.inject.Inject

import play.api.mvc._
import model.Game
import play.api.i18n.{I18nSupport, MessagesApi}

class Application @Inject() (
                              val messagesApi: MessagesApi
                            ) extends Controller with I18nSupport{

  def index(message: Option[String]) = Action {
    val games = Game.getGames
    Ok(views.html.index(games, message))
  }

}
