package model

import java.sql.Connection
import anorm.SqlParser._
import anorm._

import utilities.AnormExtension._

/**
  * Created by hki on 01-11-2016.
  */
case class Location(id: Long, name: String, current: Boolean)

object Location {
  private val location = {
    get[Long]("id") ~
    get[String]("name") ~
    get[Boolean]("current") map {
      case id ~ name ~ current => Location(id, name, current)
    }
  }

  def getLocations()(implicit c: Connection): Seq[Location] = {
    SQL(
      """
        SELECT * locations ORDER BY NAME DESC
      """.stripMargin).as(Location.location *)
  }

  def createLocationSelectTuple(locations: Seq[Location]): Seq[(String, String)] = {
    locations.map { location =>
      (location.id.toString, location.name)
    }
  }
}
