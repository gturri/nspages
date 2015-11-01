package nspages;

import java.util.ArrayList;
import java.util.List;

import org.junit.Test;

public class Test_recurseAndExcludeSubNs extends Helper {
	@Test
	public void withoutExclusion(){
		generatePage("recurse_and_exclude_ns:start", "<nspages -r -h1 -exclude>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("recurse_and_exclude_ns:subns1:subns1", "Subns1"));
		expectedLinks.add(new InternalLink("recurse_and_exclude_ns:subns2:subns2", "Subns2"));

		assertSameLinks(expectedLinks, getDriver());
	}

	@Test
	public void withExclusion(){
		generatePage("recurse_and_exclude_ns:start", "<nspages -r -h1 -exclude -exclude:subns1:>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("recurse_and_exclude_ns:subns2:subns2", "Subns2"));

		assertSameLinks(expectedLinks, getDriver());

	}
}
