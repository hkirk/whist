package model.whist

/**
  * Created by hki on 02-11-2016.
  */
sealed abstract class PointRule(val name: String, val value: String, val description: Option[String])

case object REALLY_BAD extends PointRule("Really Bad", "bad", None)
case object SOLE_TRICKS extends PointRule("Sole tricks counts", "solo", None)
case object TIPS_COUNTS extends PointRule("Tips Counts", "tips", Some("The base bid points depends on the number of tips."))

object PointRule {
  val pointRules = Seq(REALLY_BAD, SOLE_TRICKS, TIPS_COUNTS)

  val tipsMultiplier = Map(
    1 -> 1.5,
    2 -> 2,
    3 -> 3
  )

  val reallyBadPoints = 64
}
