import java.sql.Connection

import model.service.GameService
import org.scalatest.BeforeAndAfterAll
import org.scalatest.mock.MockitoSugar
import org.scalatestplus.play.{OneAppPerSuite, PlaySpec}
import play.api.db.evolutions.Evolutions
import play.api.db.{Database, Databases}
import play.api.i18n.MessagesApi
import play.api.mvc.Results
import play.api.test.FakeRequest
import play.api.test.Helpers._


/**
 * Add your spec here.
 * You can mock out a whole application including requests, plugins etc.
 * For more information, consult the wiki.
 */
class ApplicationSpec extends PlaySpec with BeforeAndAfterAll with OneAppPerSuite with Results {


  var database: Database = null

  override def beforeAll(): Unit = {
    database = Databases.inMemory(
      name = "test",
      urlOptions = Map(
      "MODE" -> "MYSQL"
      ),
      config = Map(
      "logStatements" -> true
      )
    )
  }

  override def afterAll(): Unit = {
    database.shutdown()
  }

  "Application" should {

    "Create an application controller" in {
      val messageaAPI = app.injector.instanceOf[MessagesApi]
      val applicationController = new controllers.Application(database, messageaAPI, new TestGameService())


      val result = applicationController.index(None).apply(FakeRequest())

      status(result) mustBe Ok
    }

  }

  class TestGameService extends GameService {
    override def getGames()(implicit c: Connection) = List()

    override def getGameWithPlayers(id: Long)(implicit c: Connection) = List()
  }
}
