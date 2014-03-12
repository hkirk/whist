/**
 * AnormExtension.java
 * Copyright 2014 Busywait.org. All rights reserved.
 */
package controllers.utilities

import org.joda.time._
import org.joda.time.format._
import anorm._

object AnormExtension {
  val dateFormatGeneration: DateTimeFormatter = DateTimeFormat.forPattern("yyyyMMddHHmmssSS")

  implicit def rowToTimestamp: Column[java.sql.Timestamp] = Column.nonNull { (value, meta) =>
    val MetaDataItem(qualified, nullable, clazz) = meta
    value match {
      case d: java.sql.Timestamp => Right(d)
      case _ => Left(TypeDoesNotMatch("Cannot convert " +
        value + ":" + value.asInstanceOf[AnyRef].getClass + " to timestamp for column " + qualified))
    }
  }

  implicit def rowToDateTime: Column[DateTime] = Column.nonNull { (value, meta) =>
    val MetaDataItem(qualified, nullable, clazz) = meta
    value match {
      case ts: java.sql.Timestamp => Right(new DateTime(ts.getTime))
      case d: java.sql.Date => Right(new DateTime(d.getTime))
      case str: java.lang.String => Right(dateFormatGeneration.parseDateTime(str))
      case _ => Left(TypeDoesNotMatch("Cannot convert " + value + ":" + value.asInstanceOf[AnyRef].getClass) )
    }
  }

  implicit val dateTimeToStatement = new ToStatement[DateTime] {
    def set(s: java.sql.PreparedStatement, index: Int, aValue: DateTime): Unit = {
      s.setTimestamp(index, new java.sql.Timestamp(aValue.withMillisOfSecond(0).getMillis()) )
    }
  }
}
