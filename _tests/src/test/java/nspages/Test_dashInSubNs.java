package nspages;

import java.util.ArrayList;
import java.util.List;

import org.junit.Test;

public class Test_dashInSubNs extends Helper {
	@Test
	public void withoutOption(){
		generatePage("start", "<nspages test-re>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("test-re:in_subns_with_carret", "in_subns_with_carret"));

		assertSameLinks(expectedLinks, getDriver());
	}
}
