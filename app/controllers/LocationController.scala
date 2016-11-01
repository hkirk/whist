package controllers

import javax.inject.Inject

import anorm._
import model.Location
import play.api.data.Form
import play.api.data.Forms._
import play.api.db.Database
import play.api.i18n.{I18nSupport, MessagesApi}
import play.api.mvc.{Action, Controller}

/**
  * Created by hki on 01-11-2016.
  */
class LocationController @Inject()(
                           val db: Database,
                           val messagesApi: MessagesApi
                         ) extends Controller with I18nSupport {
  val locationForm = Form(
    mapping(
      "id" -> number(-1, Int.MaxValue),
      "name" -> nonEmptyText,
      "current" -> boolean
    )(model.Location.apply)(model.Location.unapply)
  )

  def location = Action { implicit request =>
    Ok(views.html.location(locationForm))
  }

  def add = Action { implicit request =>
    locationForm.bindFromRequest.fold(
      formWithErrors => {
        BadRequest(views.html.location(formWithErrors))
      }, location => {
        saveLocation(location)
        Redirect(routes.Application.index(Some("Location is created")))
      }
    )
  }

  private def saveLocation(location: Location) = {
    db.withConnection {
      implicit c =>
        SQL(
          """
            INSERT INTO locations SET name = {name}, current = 0
          """.stripMargin).on(
          'name -> location.name).executeInsert()
    }
  }
}
