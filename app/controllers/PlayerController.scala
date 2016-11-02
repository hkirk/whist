/**
 * Player.java
 * Copyright 2014 Busywait.org. All rights reserved.
 */
package controllers

import javax.inject.Inject

import anorm.SQL
import play.api.data.Form
import play.api.data.Forms._
import play.api.db.Database
import play.api.i18n.{I18nSupport, MessagesApi}
import play.api.mvc.{Action, Controller}

class PlayerController @Inject()(
                         val db: Database,
                         val messagesApi: MessagesApi
                       ) extends Controller with I18nSupport{

  val playerForm = Form (
    mapping (
      "id" -> longNumber,
      "name" -> nonEmptyText,
      "nickname" -> nonEmptyText
    )(model.Player.apply)(model.Player.unapply)
  )

  def player = Action { implicit request =>
    Ok(views.html.player(playerForm))
  }

  def add() = Action { implicit request =>
    playerForm.bindFromRequest.fold(
      formWithErrors => {
        BadRequest(views.html.player(formWithErrors))
      }, player => {
        savePlayer(player)
        Redirect(routes.Application.index(Some("Player is saved")))
      }
    )
  }

  private def savePlayer(player: model.Player) = {
    db.withConnection {
      implicit c =>
        SQL(
          """
            INSERT INTO players SET fullname = {name}, nickname = {nickname}
          """.stripMargin).on(
            'name -> player.name,
            'nickname -> player.nickname).executeInsert()
    }
  }
}

