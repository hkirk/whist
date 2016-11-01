package model.whist

/**
  * Created by hki on 01-11-2016.
  */
sealed abstract class Suit(name: String)

case object HEARTS extends Suit("Hearts")
case object PIKES extends Suit("Pikes")
case object TILES extends Suit("Tiles")
case object CLOVERS extends Suit("Clovers")
