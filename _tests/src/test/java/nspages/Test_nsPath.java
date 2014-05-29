package nspages;

import static org.junit.Assert.*;
import java.util.ArrayList;
import java.util.List;

import org.junit.Test;

public class Test_nsPath extends Helper {
	@Test
	public void defaultPath(){
		generatePage("autrens:start", "<nspages>");
		assertSameLinks(currentNsLinks(), getDriver());
	}

	@Test
	public void explicitDefaultPath(){
		generatePage("autrens:start", "<nspages .>");
		assertSameLinks(currentNsLinks(), getDriver());
	}

	@Test
	public void unsafePath(){
		generatePage("autrens:start", "<nspages ..:..>");
		assertTrue(getDriver().getPageSource().contains("this namespace doesn't exist:"));
	}

	@Test
	public void relativePath(){
		generatePage("autrens:start", "<nspages ..:pregpages>");
		assertSameLinks(pregPagesNsLinks(), getDriver());

		generatePage("autrens:start", "<nspages .:..:pregpages>");
		assertSameLinks(pregPagesNsLinks(), getDriver());
	}

	@Test
	public void absolutePath(){
		generatePage("autrens:start", "<nspages :pregpages>");
		assertSameLinks(pregPagesNsLinks(), getDriver());
	}

	private List<InternalLink> currentNsLinks(){
		List<InternalLink> links = new ArrayList<InternalLink>();
		links.add(new InternalLink("autrens:start", "start"));
		links.add(new InternalLink("autrens:subpage", "subpage"));
		return links;
	}

	private List<InternalLink> pregPagesNsLinks(){
		List<InternalLink> links = new ArrayList<InternalLink>();
		links.add(new InternalLink("pregpages:1p", "1p"));
		links.add(new InternalLink("pregpages:1p1", "1p1"));
		links.add(new InternalLink("pregpages:p1", "p1"));
		links.add(new InternalLink("pregpages:start", "start"));
		return links;
	}
}
