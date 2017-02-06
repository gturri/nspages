package nspages;

import java.util.List;
import java.util.Set;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

public class RetrierWebDriverDecorator implements WebDriver {
	private static final int NB_MAX_RETRY = 20;
	private final WebDriver _driver;
	
	public RetrierWebDriverDecorator(WebDriver driver) {
		_driver = driver;
	}

	@Override
	public void get(String url) {
		_driver.get(url);
	}

	@Override
	public String getCurrentUrl() {
		return _driver.getCurrentUrl();
	}

	@Override
	public String getTitle() {
		return _driver.getTitle();
	}

	@Override
	public List<WebElement> findElements(By by) {
		List<WebElement> result = null;
		
		for(int i=0 ; i < NB_MAX_RETRY ; i++){
			result = _driver.findElements(by);
			if ( result != null && result.size() > 0 ){
				return result;
			}
			try {
				Thread.sleep(10);
			} catch (InterruptedException e) {}
		}
		return result;
	}

	@Override
	public WebElement findElement(By by) {
		WebElement result = null;
		Exception lastException = null;
		for ( int i=0 ; i < NB_MAX_RETRY && result == null ; i++){
			try {
				result = _driver.findElement(by);
			} catch(Exception e){
				lastException = e;
			}
		}
		
		if ( lastException != null && result == null ){
			throw new RuntimeException(lastException);
		}
		return result;
	}

	@Override
	public String getPageSource() {
		try {
			Thread.sleep(100); //To ensure we don't just get the previous page
		} catch (InterruptedException e) {}
		
		Exception lastException = null;
		String result = null;
		for ( int i=0 ; i < NB_MAX_RETRY && result == null; i++){
			try {
				result = _driver.getPageSource();
			} catch(Exception e){
				lastException = e;
			}
		}
		
		if ( result == null && lastException != null ){
			throw new RuntimeException(lastException);
		}
		return result;
	}

	@Override
	public void close() {
		_driver.close();
	}

	@Override
	public void quit() {
		_driver.quit();
	}

	@Override
	public Set<String> getWindowHandles() {
		return _driver.getWindowHandles();
	}

	@Override
	public String getWindowHandle() {
		return _driver.getWindowHandle();
	}

	@Override
	public TargetLocator switchTo() {
		return _driver.switchTo();
	}

	@Override
	public Navigation navigate() {
		return _driver.navigate();
	}

	@Override
	public Options manage() {
		return _driver.manage();
	}
}
