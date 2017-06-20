/**
 * Game.java
 * Copyright 2014 Busywait.org. All rights reserved.
 */
package model

import java.sql.{Connection, Timestamp}


case class Game(id: Long, startedAt: Timestamp, endedAt: Option[Timestamp], updatedAt: Timestamp, location: String,
                numPlayers: Long)
case class GameWithPlayers(id: Long, locationID: Long, description: String, attachments: String, point_ruls: String,
                           startedAt: Timestamp, endedAt: Option[Timestamp], location: String, playerPosition: Long,
                           playerPoints: Long, nickname: String, name: String)
case class GameForm(location: String, description: String, rule: String, attachment: String)


object Game {

}
