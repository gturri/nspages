package nspages;

import java.util.ArrayList;
import java.util.List;

import org.junit.Test;

public class Test_pregNs extends Helper {
	@Test
	public void withoutOption(){
		generatePage("pregns:start", "<nspages -subns -nopages>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("pregns:p1:start", "p1"));
		expectedLinks.add(new InternalLink("pregns:p2:start", "p2"));

		assertSameLinks(expectedLinks, getDriver());
	}

	@Test
	public void withOptionOn(){
		generatePage("pregns:start", "<nspages -pregNSOn=\"/1$/\" -subns -nopages>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("pregns:p1:start", "p1"));

		assertSameLinks(expectedLinks, getDriver());
	}

	@Test
	public void withOptionOff(){
		generatePage("pregns:start", "<nspages -pregNSOff=\"/1$/\" -subns -nopages>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("pregns:p2:start", "p2"));

		assertSameLinks(expectedLinks, getDriver());
	}
}
