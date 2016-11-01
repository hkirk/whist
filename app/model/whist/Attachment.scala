package model.whist

/**
  * Created by hki on 01-11-2016.
  */
sealed abstract class Attachment(val name: String, val multiplier: Float, val description: Option[String])

case object NONE extends Attachment("None", 1, None)
case object GOODS extends Attachment("Goods", 2, Some("Clubs are trump."))

sealed abstract class OptionalAttachment(override val name: String, override val multiplier: Float, override val description: Option[String]) extends Attachment(name, multiplier, description)
case object SANS extends OptionalAttachment("Sans", 1.5f, Some("No trump suit."))
case object TIPS extends OptionalAttachment("Tips", 1.5f, None)
case object STRONGS extends OptionalAttachment("Strongs", 1.5f, Some("Spades are trump."))
case object HALVES extends OptionalAttachment("Halves", 2, Some("The mate chooses the trump suit (The mate suit is illegal)."))

object Attachment {
  val attachmentsOrder = Seq(NONE, SANS, TIPS, STRONGS, GOODS, HALVES)
  val optionalAttachmentsOrder = Seq(SANS, TIPS, STRONGS, HALVES)
  val requiredAttachmentsOrder = Seq(NONE, GOODS)
}