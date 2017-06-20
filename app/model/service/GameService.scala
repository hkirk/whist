package model.service

import java.sql.{Connection, Timestamp}

import anorm.{SQL, ~}
import anorm.SqlParser.get
import com.google.inject.ImplementedBy
import model.{Game, GameWithPlayers}

import anorm.SqlParser._
import anorm._
import anorm.~
import utilities.AnormExtension._

@ImplementedBy(classOf[SQLGameService])
trait GameService {

  def getGames()(implicit c: Connection): List[Game]

  def getGameWithPlayers(id: Long)(implicit c: Connection): List[GameWithPlayers]

}

class SQLGameService extends GameService {

  def getGames()(implicit c: Connection): List[Game] = {
    SQL(
      """
      SELECT g.id AS id, g.started_at AS started_at, g.ended_at AS ended_at, g.updated_at AS updated_at,
             l.name AS location, (SELECT COUNT(*)
                                    FROM game_players AS gp
                                    WHERE gp.game_id = g.id
                                  ) AS n_players
      FROM games AS g
      LEFT OUTER JOIN locations l ON g.location_id = l.id
      ORDER BY g.started_at DESC
      """.stripMargin).as(SQLGameService.game *)
  }

  def getGameWithPlayers(id: Long)(implicit c: Connection): List[GameWithPlayers] = {
    SQL(
      """
        SELECT
          g.*,
          l.name             AS location,
          gp.player_position AS player_position,
          gp.total_points    AS player_total_points,
          p.nickname         AS player_nickname,
          p.fullname         AS player_fullname
        FROM             games        AS g
        LEFT OUTER JOIN  locations    AS l   ON l.id = g.location_id
        INNER JOIN       game_players AS gp  ON gp.game_id = g.id
        LEFT OUTER JOIN  players      AS p   ON p.id = gp.player_id
        WHERE g.id = {id}
        ORDER BY gp.player_position ASC
      """.stripMargin).on('id -> id).as(SQLGameService.gameWithPlayers *)
  }

}

object SQLGameService {
  private val game = {
    get[Long]("id") ~
      get[Timestamp]("started_at") ~
      get[Option[Timestamp]]("ended_at") ~
      get[Timestamp]("updated_at") ~
      get[String]("locations.name") ~
      get[Long]("n_players") map {
      case id ~ started ~ ended ~ updated ~ name ~ players => Game(id, started, ended, updated, name, players)
    }
  }

  private val gameWithPlayers = {
    get[Long]("id") ~
      get[Long]("location_id") ~
      get[String]("description") ~
      get[String]("attachments") ~
      get[String]("point_rules") ~
      get[Timestamp]("started_at") ~
      get[Option[Timestamp]]("ended_at") ~
      get[String]("locations.name") ~
      get[Long]("game_players.player_position") ~
      get[Long]("game_players.total_points") ~
      get[String]("players.nickname") ~
      get[String]("players.fullname") map {
      case id ~ locationID ~ description ~ attachments ~ pointRules ~ startedAt ~ endedAt ~ location ~ playerPos
        ~ playerPoints ~ nickname ~ name => GameWithPlayers(id, locationID, description, attachments, pointRules,
        startedAt, endedAt, location, playerPos, playerPoints, nickname, name)
    }
  }
}
