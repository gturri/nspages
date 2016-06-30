package nspages;

import java.util.ArrayList;
import java.util.List;

import org.junit.Test;

public class Test_recursion extends Helper {
	@Test
	public void withoutOption(){
		generatePage("recursion:start", "<nspages -subns>");
		assertSameNsAndPagesLinks(level1NsLinks(), level1PagesLinks());
	}

	@Test
	public void levelOfRecursionOf1IsSameAsDefault(){
		generatePage("recursion:start", "<nspages -subns -r 1>");
		assertSameNsAndPagesLinks(level1NsLinks(), level1PagesLinks());
	}

	@Test
	public void levelOfRecursionLimited(){
		generatePage("recursion:start", "<nspages -subns -r 2>");
		assertSameNsAndPagesLinks(level2NsLinks(), level2PagesLinks());
	}

	@Test
	public void unlimitedRecursion(){
		generatePage("recursion:start", "<nspages -subns -r 0>");
		assertSameNsAndPagesLinks(allNsLinks(), allPagesLinks());
	}

	@Test
	public void alternativeSyntaxes(){
		generatePage("recursion:start", "<nspages -subns -r=2>");
		assertSameNsAndPagesLinks(level2NsLinks(), level2PagesLinks());

		generatePage("recursion:start", "<nspages -subns -r \"2\">");
		assertSameNsAndPagesLinks(level2NsLinks(), level2PagesLinks());

		generatePage("recursion:start", "<nspages -subns -r = \"2\">");
		assertSameNsAndPagesLinks(level2NsLinks(), level2PagesLinks());
	}

	@Test
	//Because we had conflict in option parsing. See #30
	public void alongWithExcludeOption(){
		generatePage("recursion:start", "<nspages -subns -r -exclude:start>");
		assertSameNsAndPagesLinks(allNsLinks(), allPagesLinks(true));
	}

	private List<InternalLink> allNsLinks(){
		return level2NsLinks();
	}

	private List<InternalLink> allPagesLinks(){
		return allPagesLinks(false);
	}

	private List<InternalLink> allPagesLinks(boolean excludeStart){
		List<InternalLink> links = level2PagesLinks(excludeStart);
		links.add(2, new InternalLink("recursion:lvl2:lvl3:pagelvl3", "pagelvl3"));
		return links;
	}

	private List<InternalLink> level2NsLinks(){
		List<InternalLink> links = level1NsLinks();
		links.add(new InternalLink("recursion:lvl2:lvl3:start", "lvl3"));
		return links;
	}

	private List<InternalLink> level2PagesLinks(){
		return level2PagesLinks(false);
	}

	private List<InternalLink> level2PagesLinks(boolean excludeStart){
		List<InternalLink> links = level1PagesLinks(excludeStart);
		links.add(1, new InternalLink("recursion:lvl2:pagelvl2", "pagelvl2"));
		return links;
	}

	private List<InternalLink> level1NsLinks(){
		List<InternalLink> links = new ArrayList<InternalLink>();
		links.add(new InternalLink("recursion:lvl2:start", "lvl2"));
		return links;
	}

	private List<InternalLink> level1PagesLinks(){
		return level1PagesLinks(false);
	}

	private List<InternalLink> level1PagesLinks(boolean excludeStart){
		List<InternalLink> links = new ArrayList<InternalLink>();
		links.add(new InternalLink("recursion:pagelvl1", "pagelvl1"));
		if ( ! excludeStart ){
			links.add(new InternalLink("recursion:start", "start"));
		}
		return links;
	}



	//<nspages -subns>
	//<nspages -subns -R 1>
	//<nspages -subns -r 2>
	//<nspages -subns -r>
	//<nspages -subns -r = "2">
}
