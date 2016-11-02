package model.whist

/**
  * Created by hki on 01-11-2016.
  */
sealed abstract class Attachment(val name: String, val value: String, val multiplier: Float, val description: Option[String])

case object NONE extends Attachment("None", "none", 1, None)
case object GOODS extends Attachment("Goods", "goods", 2, Some("Clubs are trump."))

sealed abstract class OptionalAttachment(override val name: String, override val value: String, override val multiplier: Float, override val description: Option[String]) extends Attachment(name, value, multiplier, description)
case object SANS extends OptionalAttachment("Sans", "sans", 1.5f, Some("No trump suit."))
case object TIPS extends OptionalAttachment("Tips", "tips", 1.5f, None)
case object STRONGS extends OptionalAttachment("Strongs", "strongs", 1.5f, Some("Spades are trump."))
case object HALVES extends OptionalAttachment("Halves", "halves", 2, Some("The mate chooses the trump suit (The mate suit is illegal)."))

object Attachment {
  val attachmentsOrder = Seq(NONE, SANS, TIPS, STRONGS, GOODS, HALVES)
  val optionalAttachmentsOrder = Seq(SANS, TIPS, STRONGS, HALVES)
  val requiredAttachmentsOrder = Seq(NONE, GOODS)
}