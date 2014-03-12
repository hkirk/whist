/**
 * Player.java
 * Copyright 2014 Busywait.org. All rights reserved.
 */
package controllers

import play.api.mvc.{Action, Controller}
import play.api.data.Form
import play.api.data.Forms.{mapping, nonEmptyText}
import play.api.db.DB
import play.api.Play.current

import anorm.SQL

object Player extends Controller {

  val playerForm = Form (
    mapping (
      "name" -> nonEmptyText,
      "nickname" -> nonEmptyText
    )(model.Player.apply)(model.Player.unapply)
  )

  def player = Action {
    Ok(views.html.player(playerForm))
  }

  def addPlayer = Action { implicit request =>
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
    DB.withConnection {
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

