name := "whistCalculator"

version := "1.0"

lazy val root = (project in file(".")).enablePlugins(PlayScala)

scalaVersion := "2.11.8"

libraryDependencies ++= Seq(
  jdbc,
  cache,
  evolutions)

libraryDependencies ++= Seq(
  "com.typesafe.play" %% "anorm" % "2.4.0",
  "mysql" % "mysql-connector-java" % "5.1.21",
  "org.scalatestplus.play" %% "scalatestplus-play" % "1.5.1" % "test"
)

scalacOptions ++= Seq("-feature", "-deprecation")

// Play provides two styles of routers, one expects its actions to be injected, the
// other, legacy style, accesses its actions statically.
routesGenerator := InjectedRoutesGenerator
