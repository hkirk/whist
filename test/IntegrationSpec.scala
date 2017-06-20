import org.scalatestplus.play.PlaySpec
import play.test.WithBrowser

class IntegrationSpec extends PlaySpec {

  "Application" should {

    "work from within a browser" in new WithBrowser {

      browser.goTo("http://localhost:" + port)

      browser.pageSource must contain("Your new application is ready.")
    }
  }
}
