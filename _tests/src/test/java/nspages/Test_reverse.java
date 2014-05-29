package nspages;

import java.util.ArrayList;
import java.util.List;

import org.junit.Test;

import com.google.common.collect.Lists;

public class Test_reverse extends Helper {
	@Test
	public void withoutOption(){
		generatePage("reverse:start", "<nspages .>");
		assertSameLinks(currentLinks(), getDriver());
	}

	@Test
	public void withOption(){
		generatePage("reverse:start", "<nspages -reverse>");
		assertSameLinks(Lists.reverse(currentLinks()), getDriver());
	}

	private List<InternalLink> currentLinks(){
		List<InternalLink> links = new ArrayList<InternalLink>();
		links.add(new InternalLink("reverse:a", "a"));
		links.add(new InternalLink("reverse:b", "b"));
		links.add(new InternalLink("reverse:c", "c"));
		links.add(new InternalLink("reverse:start", "start"));
		return links;
	}
}
