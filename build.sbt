import play.Project._

name := "whistCalculator"

version := "1.0"

playScalaSettings

libraryDependencies ++= Seq(
  jdbc,
  anorm,
  cache,
  "mysql" % "mysql-connector-java" % "5.1.21"
)

scalacOptions ++= Seq("-feature", "-deprecation")
