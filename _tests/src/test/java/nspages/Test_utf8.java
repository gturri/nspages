package nspages;

import java.util.ArrayList;
import java.util.List;

import org.junit.Test;

public class Test_utf8 extends Helper {
	@Test
	public void nsWithSpecialChars(){
		generatePage("utf8:start", "<nspages>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("utf8:ea", "ea"));
		expectedLinks.add(new InternalLink("utf8:e%E0%AC%8Bae", "eଋae"));
		expectedLinks.add(new InternalLink("utf8:start", "start"));
		expectedLinks.add(new InternalLink("utf8:%E0%AC%8Beae", "ଋeae"));
		expectedLinks.add(new InternalLink("utf8:%F0%90%A4%81eae", "𐤁eae"));

		assertSameLinks(expectedLinks, getDriver());

	}
}
