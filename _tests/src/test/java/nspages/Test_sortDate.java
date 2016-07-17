package nspages;

import java.util.ArrayList;
import java.util.List;

import org.junit.Test;

public class Test_sortDate extends Helper {
	@Test
	public void withoutOption(){
		generatePage("ns1:start", "<nspages -h1 -exclude>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("ns1:a", "a"));
		expectedLinks.add(new InternalLink("ns1:b1", "b1"));
		expectedLinks.add(new InternalLink("ns1:b2", "b2"));
		expectedLinks.add(new InternalLink("ns1:c", "c"));

		assertSameLinks(expectedLinks);
	}

	@Test
	public void withOption(){
		generatePage("ns1:start", "<nspages -h1 -sortDate -exclude>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("ns1:a", "a"));
		expectedLinks.add(new InternalLink("ns1:b2", "b2"));
		expectedLinks.add(new InternalLink("ns1:c", "c"));
		expectedLinks.add(new InternalLink("ns1:b1", "b1"));

		assertSameLinks(expectedLinks);
	}

	@Test
	public void withNbMaxItems(){
		generatePage("ns1:start", "<nspages -h1 -sortDate -exclude -nbItemsMax=3>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("ns1:a", "a"));
		expectedLinks.add(new InternalLink("ns1:b2", "b2"));
		expectedLinks.add(new InternalLink("ns1:c", "c"));

		assertSameLinks(expectedLinks);
	}

}
