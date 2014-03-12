package controllers

import play.api._
import play.api.mvc._
import play.api.db.DB
import play.api.Play.current

import anorm._

import model.Game


object Application extends Controller {

  def index(message: Option[String]) = Action {
    val games = Game.getGames
    Ok(views.html.index(games, message))
  }

}
