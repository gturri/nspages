package nspages;

import java.util.ArrayList;
import java.util.List;

import org.junit.Test;

public class Test_recursion extends Helper {
	@Test
	public void withoutOption(){
		generatePage("recursion:start", "<nspages -subns>");
		assertSameLinks(level1NsLinks(), level1PagesLinks(), getDriver());
	}

	@Test
	public void levelOfRecursionOf1IsSameAsDefault(){
		generatePage("recursion:start", "<nspages -subns -r 1>");
		assertSameLinks(level1NsLinks(), level1PagesLinks(), getDriver());
	}

	@Test
	public void levelOfRecursionLimited(){
		generatePage("recursion:start", "<nspages -subns -r 2>");
		assertSameLinks(level2NsLinks(), level2PagesLinks(), getDriver());
	}

	@Test
	public void unlimitedRecursion(){
		generatePage("recursion:start", "<nspages -subns -r 0>");
		assertSameLinks(allNsLinks(), allPagesLinks(), getDriver());
	}

	@Test
	public void alternativeSyntaxes(){
		generatePage("recursion:start", "<nspages -subns -r=2>");
		assertSameLinks(level2NsLinks(), level2PagesLinks(), getDriver());

		generatePage("recursion:start", "<nspages -subns -r \"2\">");
		assertSameLinks(level2NsLinks(), level2PagesLinks(), getDriver());

		generatePage("recursion:start", "<nspages -subns -r = \"2\">");
		assertSameLinks(level2NsLinks(), level2PagesLinks(), getDriver());
	}

	private List<InternalLink> allNsLinks(){
		return level2NsLinks();
	}

	private List<InternalLink> allPagesLinks(){
		List<InternalLink> links = level2PagesLinks();
		links.add(2, new InternalLink("recursion:lvl2:lvl3:pagelvl3", "pagelvl3"));
		return links;
	}

	private List<InternalLink> level2NsLinks(){
		List<InternalLink> links = level1NsLinks();
		links.add(new InternalLink("recursion:lvl2:lvl3:start", "lvl3"));
		return links;
	}

	private List<InternalLink> level2PagesLinks(){
		List<InternalLink> links = level1PagesLinks();
		links.add(1, new InternalLink("recursion:lvl2:pagelvl2", "pagelvl2"));
		return links;
	}

	private List<InternalLink> level1NsLinks(){
		List<InternalLink> links = new ArrayList<InternalLink>();
		links.add(new InternalLink("recursion:lvl2:start", "lvl2"));
		return links;
	}

	private List<InternalLink> level1PagesLinks(){
		List<InternalLink> links = new ArrayList<InternalLink>();
		links.add(new InternalLink("recursion:pagelvl1", "pagelvl1"));
		links.add(new InternalLink("recursion:start", "start"));
		return links;
	}



	//<nspages -subns>
	//<nspages -subns -R 1>
	//<nspages -subns -r 2>
	//<nspages -subns -r>
	//<nspages -subns -r = "2">
}
